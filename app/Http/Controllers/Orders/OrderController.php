<?php

namespace App\Http\Controllers\Orders;

use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderIndexRequest;
use App\Http\Requests\Orders\OrderRequest;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request): Response
    {
        $query = match ($type = $request->getOrderType()) {
            OrderType::CLIENT->value => Order::clientOrders()->with(['client']),
            OrderType::SUPPLIER->value => Order::supplierOrders()->with(['supplier']),
        };

        if ($searchTerm = $request->getSearchTerm()) {
            $query->where(function ($q) use ($searchTerm, $type) {
                $q->where('order_number', 'like', "%$searchTerm%");

                if ($type === OrderType::CLIENT->value) {
                    $q->orWhereHas('client', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'like', "%$searchTerm%");
                    });
                } else {
                    $q->orWhereHas('supplier', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'like', "%$searchTerm%");
                    });
                }
            });
        }

        $sortBy = $request->getSortBy();
        $sortDirection = $request->getSortDirection();
        if ($sortBy && $sortDirection) {
            switch ($sortBy) {
                case 'total_amount':
                    $query->orderByTotalAmount($sortDirection);
                    break;

                case 'item_count':
                    $query->orderByItemCount($sortDirection);
                    break;

                default:
                    $query->orderBy($sortBy, $sortDirection);
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($startDate = $request->getStartDate()) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate = $request->getEndDate()) {
            $query->where('created_at', '<=', $endDate);
        }

        $paginated = $this->paginate($request, $query);

        return Inertia::render('orders/Orders', [
            'items' => $paginated['items'],
            'pagination' => $paginated['pagination'],
            'type' => $type,
        ]);
    }

    public function create()
    {
        return Inertia::render('orders/CreateOrder');
    }

    public function store(OrderRequest $request) {}

    public function show(Request $request, string $id)
    {
        $query = match ($request->query('type', 'client')) {
            OrderType::CLIENT->value => Order::clientOrders()->with(['client']),
            OrderType::SUPPLIER->value => Order::supplierOrders()->with(['supplier']),
        };

        $order = $query->findOrFail($id);

        return Inertia::render('orders/ShowOrder', [
            'order' => $order,
        ]);
    }

    public function edit($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
