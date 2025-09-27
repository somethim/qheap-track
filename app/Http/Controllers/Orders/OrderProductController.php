<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderProductRequest;
use App\Models\Orders\OrderProduct;

class OrderProductController extends Controller
{
    public function index()
    {
        return OrderProduct::all();
    }

    public function store(OrderProductRequest $request)
    {
        return OrderProduct::create($request->validated());
    }

    public function show(OrderProduct $orderProduct)
    {
        return $orderProduct;
    }

    public function update(OrderProductRequest $request, OrderProduct $orderProduct)
    {
        $orderProduct->update($request->validated());

        return $orderProduct;
    }

    public function destroy(OrderProduct $orderProduct)
    {
        $orderProduct->delete();

        return response()->json();
    }
}
