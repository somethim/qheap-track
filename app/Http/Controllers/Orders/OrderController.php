<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderRequest;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $this->paginate($request, Order::clientOrders()->get()->toArray());

        return Inertia::render('orders/Orders', ['paginated' => $orders]);
    }

    public function create(): Response
    {
        return Inertia::render('orders/CreateOrder');
    }

    public function store(OrderRequest $request) {}

    public function show($id) {}

    public function edit($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
