<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ClientRequest;
use App\Http\Requests\Search\ClientAndSupplierSearchRequest;
use App\Models\Orders\Client;
use App\Utils\Currency;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(ClientAndSupplierSearchRequest $request)
    {
        $query = Client::withCount('orders')->newQuery();

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

        return Inertia::render('clients/index', [
            'items' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ]);
    }

    public function store(ClientRequest $request)
    {
        $client = Client::create($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    public function create()
    {
        return Inertia::render('clients/create');
    }

    public function show(Client $client)
    {
        $client->load('orders');

        return Inertia::render('clients/show', [
            'client' => [
                ...$client->toArray(),
                'formatted_cost' => Currency::formatCurrency($client->orders->sum('cost')),
            ],
        ]);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    public function search(ClientAndSupplierSearchRequest $request): JsonResponse
    {
        $query = Client::query();

        if ($term = $request->getSearchTerm()) {
            $sanitizedTerm = "%$term%";
            $query->where(function ($q) use ($sanitizedTerm) {
                $q->where('name', 'like', $sanitizedTerm)
                    ->orWhere('contact_email', 'like', $sanitizedTerm)
                    ->orWhere('contact_phone', 'like', $sanitizedTerm);
            });
        }

        return response()->json([
            'clients' => $query->orderBy('name')->get()->toArray(),
        ]);
    }
}
