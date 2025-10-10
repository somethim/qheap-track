<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ProductRequest;
use App\Http\Requests\Search\ProductSearchRequest;
use App\Models\Orders\Product;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(ProductSearchRequest $request)
    {
        $query = Product::query();

        if ($request->getSearchTerm()) {
            $term = '%'.$request->getSearchTerm().'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        if ($startDate = $request->getStartDate()) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate = $request->getEndDate()) {
            $query->where('created_at', '<=', $endDate);
        }

        $sortBy = $request->getSortBy();
        $sortDirection = $request->getSortDirection();
        if ($sortBy && $sortDirection) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $paginated = $this->paginate($request, $query);

        return Inertia::render('products/index', [
            'items' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ]);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create([...$request->validated(), 'user_id' => auth()->id()]);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function create()
    {
        return Inertia::render('products/create');
    }

    public function show(Product $product)
    {
        return Inertia::render('products/show', ['product' => $product]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function search(ProductSearchRequest $request): JsonResponse
    {
        if ($name = $request->getName()) {
            return response()->json(['products' => Product::where('name', 'like', "%$name%")->orderBy('name')->get()->toArray()]);
        }

        if ($sku = $request->getSku()) {
            return response()->json(['products' => Product::where('sku', 'like', "%$sku%")->orderBy('name')->get()->toArray()]);
        }

        return response()->json(['products' => Product::orderBy('name')->get()->toArray()]);
    }
}
