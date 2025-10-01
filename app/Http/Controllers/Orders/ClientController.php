<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ClientRequest;
use App\Http\Requests\Orders\SearchRequest;
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

    public function search(SearchRequest $request): JsonResponse
    {
        if (! $term = $request->getSearchTerm()) {
            return response()->json(Client::orderBy('name')->get()->toArray());
        }

        $sanitizedTerm = trim($term);

        $searchPattern = "%$sanitizedTerm%";

        return response()->json(
            Client::where('name', 'like', $searchPattern)
                ->orWhere('contact_email', 'like', $searchPattern)
                ->orWhere('contact_phone', 'like', $searchPattern)
                ->orderBy('name')
                ->get()->toArray()
        );
    }
}
