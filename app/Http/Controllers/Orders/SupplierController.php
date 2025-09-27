<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\SupplierRequest;
use App\Models\Orders\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        return Supplier::all();
    }

    public function store(SupplierRequest $request)
    {
        return Supplier::create($request->validated());
    }

    public function show(Supplier $supplier)
    {
        return $supplier;
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return $supplier;
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json();
    }
}
