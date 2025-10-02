<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ClientRequest;
use App\Http\Requests\Search\ClientAndSupplierSearchRequest;
use App\Models\Orders\Client;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index() {}

    public function create() {}

    public function store(ClientRequest $request) {}

    public function show(string $id) {}

    public function update(ClientRequest $request, string $id) {}

    public function destroy(string $id) {}

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
