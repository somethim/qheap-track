<?php

namespace App\Http\Controllers\Orders;

use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderRequest;
use App\Http\Requests\Search\OrderIndexRequest;
use App\Models\Orders\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request): Response
    {
        $query = match ($type = $request->getOrderType()) {
            OrderType::CLIENT->value => Order::clientOrders()->with('client'),
            OrderType::SUPPLIER->value => Order::supplierOrders()->with('supplier'),
        };

        if ($searchTerm = $request->getSearchTerm()) {
            $sanitizedTerm = trim($searchTerm);
            $searchPattern = "%$sanitizedTerm%";

            $query->where(function ($q) use ($searchPattern, $type) {
                $q->where('order_number', 'like', $searchPattern);

                if ($type === OrderType::CLIENT->value) {
                    $q->orWhereHas('client', function ($q2) use ($searchPattern) {
                        $q2->where('name', 'like', $searchPattern);
                    });
                } else {
                    $q->orWhereHas('supplier', function ($q2) use ($searchPattern) {
                        $q2->where('name', 'like', $searchPattern);
                    });
                }
            });
        }

        $sortBy = $request->getSortBy();
        $sortDirection = $request->getSortDirection();
        if ($sortBy && $sortDirection) {
            switch ($sortBy) {
                case 'cost':
                    $query->orderByTotalAmount($sortDirection);
                    break;

                case 'stock':
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

        return Inertia::render('orders/index', [
            'items' => $paginated['items'],
            'pagination' => $paginated['pagination'],
            'type' => $type,
        ]);
    }

    public function create()
    {
        return Inertia::render('orders/create');
    }

    public function store(OrderRequest $request) {}

    public function show(string $id)
    {
        return Inertia::render('orders/show', [
            'order' => Order::with(['orderProducts.product', 'client', 'supplier'])->findOrFail($id),
        ]);
    }

    public function update(OrderRequest $request, string $id) {}

    public function destroy(string $id) {}
}
