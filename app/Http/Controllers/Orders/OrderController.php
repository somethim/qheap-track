<?php

namespace App\Http\Controllers\Orders;

use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderRequest;
use App\Http\Requests\Search\OrderIndexRequest;
use App\Models\Orders\Order;
use App\Models\Orders\Product;
use App\Utils\Currency;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request): Response
    {
        $query = match ($type = $request->getOrderType()) {
            OrderType::CLIENT->value => Order::clientOrders()->with('client'),
            OrderType::SUPPLIER->value => Order::supplierOrders()->with('supplier'),
        };

        if ($searchTerm = $request->getSearchTerm()) {
            $searchPattern = "%$searchTerm%";

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
                case 'cost':
                    $query->orderByTotalAmount($sortDirection);
                    break;

                case 'stock':
                    $query->orderByItemCount($sortDirection);
                    break;

                case 'client_name':
                    $query->join('clients', 'orders.client_id', '=', 'clients.id')
                        ->orderBy('clients.name', $sortDirection)
                        ->select('orders.*');
                    break;

                case 'supplier_name':
                    $query->join('suppliers', 'orders.supplier_id', '=', 'suppliers.id')
                        ->orderBy('suppliers.name', $sortDirection)
                        ->select('orders.*');
                    break;

                default:
                    $query->orderBy($sortBy, $sortDirection);
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $paginated = $this->paginate($request, $query);

        $items = array_map(function ($order) {
            return [
                ...$order->toArray(),
                'formatted_cost' => Currency::formatCurrency($order->cost),
            ];
        }, $paginated['items']);

        return Inertia::render('orders/index', [
            'items' => $items,
            'pagination' => $paginated['pagination'],
            'type' => $type,
        ]);
    }

    public function store(OrderRequest $request)
    {
        try {
            $order = DB::transaction(function () use ($request) {
                $order = Order::create(Arr::except($request->validated(), ['order_products', 'contact_info']));

                $orderProducts = array_map(function ($orderProduct) use ($order) {
                    return [
                        'order_id' => $order->id,
                        'product_id' => $orderProduct['id'],
                        'stock' => $orderProduct['stock'] ?? 0,
                        'price' => $orderProduct['price'] ?? 0,
                    ];
                }, $request->validated('order_products'));

                $order->orderProducts()->createMany($orderProducts);

                $stockChanges = collect($orderProducts)->groupBy('product_id')->map(function ($items) {
                    return $items->sum('stock');
                });

                $products = Product::whereIn('id', $stockChanges->keys())->get();
                $multiplier = $order->isClientOrder() ? -1 : 1;

                foreach ($products as $product) {
                    $product->stock += $stockChanges[$product->id] * $multiplier;
                    $product->save();
                }

                $this->updateContactInfo($request, $order);

                return $order;
            });

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order created successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred while creating the order: '.$e->getMessage())
                ->withInput();
        }
    }

    public function create()
    {
        return Inertia::render('orders/create');
    }

    public function show(Order $order)
    {
        return Inertia::render('orders/show', [
            'order' => $order->load(['orderProducts.product', 'client', 'supplier']),
        ]);
    }

    public function update(OrderRequest $request, Order $order)
    {
        try {
            DB::transaction(function () use ($request, $order) {
                $this->updateOrderData($request, $order);
                $this->updateContactInfo($request, $order);
            });

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order updated successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the order: '.$e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Order $order)
    {
        try {
            $type = $order->isClientOrder() ? OrderType::CLIENT->value : OrderType::SUPPLIER->value;

            DB::transaction(function () use ($order) {
                $orderProducts = $order->orderProducts()->get();
                $multiplier = $order->isClientOrder() ? -1 : 1;

                $stockChanges = $orderProducts->groupBy('product_id')->map(function ($items) {
                    return $items->sum('stock');
                });

                $products = Product::whereIn('id', $stockChanges->keys())->get();
                foreach ($products as $product) {
                    $product->stock -= $stockChanges[$product->id] * $multiplier;
                    $product->save();
                }

                $order->orderProducts()->delete();

                $order->delete();
            });

            return redirect()
                ->route('orders.index', ['type' => $type])
                ->with('success', 'Order deleted successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the order: '.$e->getMessage());
        }
    }

    private function updateOrderData(OrderRequest $request, Order $order): void
    {
        $oldOrderProducts = $order->orderProducts()->get();
        $oldMultiplier = $order->isClientOrder() ? -1 : 1;

        $oldStockChanges = $oldOrderProducts->groupBy('product_id')->map(function ($items) {
            return $items->sum('stock');
        });

        $productsToUpdate = Product::whereIn('id', $oldStockChanges->keys())->get();
        foreach ($productsToUpdate as $product) {
            $product->stock -= $oldStockChanges[$product->id] * $oldMultiplier;
            $product->save();
        }

        $order->orderProducts()->delete();

        $order->update(Arr::except($request->validated(), ['order_products', 'contact_info']));

        $newOrderProducts = array_map(function ($orderProduct) use ($order) {
            return [
                'order_id' => $order->id,
                'product_id' => $orderProduct['id'],
                'stock' => $orderProduct['stock'] ?? 0,
                'price' => $orderProduct['price'] ?? 0,
            ];
        }, $request->validated('order_products'));

        $order->orderProducts()->createMany($newOrderProducts);

        $newStockChanges = collect($newOrderProducts)->groupBy('product_id')->map(function ($items) {
            return $items->sum('stock');
        });

        $newMultiplier = $order->isClientOrder() ? -1 : 1;
        $productsToUpdate = Product::whereIn('id', $newStockChanges->keys())->get();

        foreach ($productsToUpdate as $product) {
            $product->stock += $newStockChanges[$product->id] * $newMultiplier;
            $product->save();
        }
    }

    private function updateContactInfo(OrderRequest $request, Order $order): void
    {
        if (! $request->has('contact_info')) {
            return;
        }

        $contactInfo = $request->validated('contact_info');

        if ($order->client_id) {
            $order->client->update($contactInfo);
        } elseif ($order->supplier_id) {
            $order->supplier->update($contactInfo);
        }
    }
}
