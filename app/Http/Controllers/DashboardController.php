<?php

namespace App\Http\Controllers;

use App\Models\Orders\Client;
use App\Models\Orders\Order;
use App\Models\Orders\Product;
use App\Models\Orders\Supplier;
use App\Utils\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = $this->getStatistics();

        $monthlyData = $this->getMonthlyData();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'monthlyData' => $monthlyData,
        ]);
    }

    private function getStatistics(): array
    {
        $clientOrders = Order::clientOrders()
            ->with('orderProducts')
            ->get();

        $clientOrdersCount = $clientOrders->count();
        $totalRevenue = $clientOrders->sum('cost');

        $supplierOrders = Order::supplierOrders()
            ->with('orderProducts')
            ->get();

        $supplierOrdersCount = $supplierOrders->count();
        $totalExpenses = $supplierOrders->sum('cost');

        $profit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        $clientsCount = Client::count();
        $suppliersCount = Supplier::count();
        $productsCount = Product::count();

        $recentClientOrders = Order::clientOrders()
            ->with(['client', 'orderProducts'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentSupplierOrders = Order::supplierOrders()
            ->with(['supplier', 'orderProducts'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return [
            'revenue' => [
                'total' => $totalRevenue,
                'totalFormatted' => Currency::formatCurrency($totalRevenue),
                'count' => $clientOrdersCount,
            ],
            'expenses' => [
                'total' => $totalExpenses,
                'totalFormatted' => Currency::formatCurrency($totalExpenses),
                'count' => $supplierOrdersCount,
            ],
            'profit' => [
                'total' => $profit,
                'totalFormatted' => Currency::formatCurrency($profit),
                'margin' => round($profitMargin, 2),
            ],
            'entities' => [
                'clients' => $clientsCount,
                'suppliers' => $suppliersCount,
                'products' => $productsCount,
            ],
            'recentClientOrders' => $recentClientOrders,
            'recentSupplierOrders' => $recentSupplierOrders,
        ];
    }

    private function getMonthlyData(): array
    {
        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            $months[] = [
                'key' => $monthKey,
                'label' => $monthLabel,
            ];
        }

        $clientOrdersByMonth = Order::clientOrders()
            ->select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $supplierOrdersByMonth = Order::supplierOrders()
            ->select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $revenueByMonth = Order::clientOrders()
            ->select(
                DB::raw("strftime('%Y-%m', orders.created_at) as month"),
                DB::raw('COALESCE(SUM(order_products.stock * order_products.price), 0) as total')
            )
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $expensesByMonth = Order::supplierOrders()
            ->select(
                DB::raw("strftime('%Y-%m', orders.created_at) as month"),
                DB::raw('COALESCE(SUM(order_products.stock * order_products.price), 0) as total')
            )
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        foreach ($months as $month) {
            $key = $month['key'];
            $data[] = [
                'month' => $month['label'],
                'revenue' => $revenueByMonth[$key] ?? 0,
                'revenueFormatted' => Currency::formatCurrency($revenueByMonth[$key] ?? 0),
                'expenses' => $expensesByMonth[$key] ?? 0,
                'expensesFormatted' => Currency::formatCurrency($expensesByMonth[$key] ?? 0),
                'clientOrders' => $clientOrdersByMonth[$key] ?? 0,
                'supplierOrders' => $supplierOrdersByMonth[$key] ?? 0,
            ];
        }

        return $data;
    }
}
