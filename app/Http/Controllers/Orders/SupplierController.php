<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\SupplierRequest;
use App\Http\Requests\Search\ClientAndSupplierSearchRequest;
use App\Models\Orders\Supplier;
use App\Utils\Currency;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(ClientAndSupplierSearchRequest $request)
    {
        $query = Supplier::withCount('orders')->newQuery();

        if ($request->getSearchTerm()) {
            $term = '%'.$request->getSearchTerm().'%';
            $query->where('name', 'like', $term)
                ->orWhere('contact_email', 'like', $term)
                ->orWhere('contact_phone', 'like', $term);
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
            switch ($sortBy) {
                case 'order_count':
                    $query->orderBy('orders_count', $sortDirection);
                    break;

                default:
                    $query->orderBy($sortBy, $sortDirection);
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $paginated = $this->paginate($request, $query);

        return Inertia::render('suppliers/index', [
            'items' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ]);
    }

    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', 'Supplier created successfully.');
    }

    public function create()
    {
        return Inertia::render('suppliers/create');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('orders');

        return Inertia::render('suppliers/show', [
            'supplier' => [
                ...$supplier->toArray(),
                'formatted_cost' => Currency::formatCurrency($supplier->orders->sum('cost')),
            ],
        ]);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function search(ClientAndSupplierSearchRequest $request): JsonResponse
    {
        $query = Supplier::query();

        if ($term = $request->getSearchTerm()) {
            $sanitizedTerm = "%$term%";
            $query->where(function ($q) use ($sanitizedTerm) {
                $q->where('name', 'like', $sanitizedTerm)
                    ->orWhere('contact_email', 'like', $sanitizedTerm)
                    ->orWhere('contact_phone', 'like', $sanitizedTerm);
            });
        }

        return response()->json([
            'suppliers' => $query->orderBy('name')->get()->toArray(),
        ]);
    }
}
