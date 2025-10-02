<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ProductRequest;
use App\Http\Requests\Search\ProductSearchRequest;
use App\Models\Orders\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index() {}

    public function create() {}

    public function store(ProductRequest $request) {}

    public function show(string $id) {}

    public function update(ProductRequest $request, string $id) {}

    public function destroy(string $id) {}

    public function search(ProductSearchRequest $request): JsonResponse
    {
        if ($name = $request->getName()) {
            $sanitizedName = "%$name%";

            return response()->json(['products' => Product::where('name', 'like', $sanitizedName)->orderBy('name')->get()->toArray()]);
        }

        if ($sku = "%{$request->getSku()}%") {
            $sanitizedSku = "%$sku%";

            return response()->json(['products' => Product::where('sku', 'like', $sanitizedSku)->orderBy('name')->get()->toArray()]);
        }

        return response()->json(['products' => Product::orderBy('name')->get()->toArray()]);
    }
}
