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
        $type = $request->getOrderType();
        $startDate = $request->getStartDate();
        $endDate = $request->getEndDate();

        $query = match ($type) {
            OrderType::CLIENT->value => Order::clientOrders()->with(['client']),
            OrderType::SUPPLIER->value => Order::supplierOrders()->with(['supplier']),
        };

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
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

    public function show(Request $request, int $id)
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
