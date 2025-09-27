<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ClientRequest;
use App\Models\Orders\Client;

class ClientController extends Controller
{
    public function index()
    {
        return Client::all();
    }

    public function store(ClientRequest $request)
    {
        return Client::create($request->validated());
    }

    public function show(Client $client)
    {
        return $client;
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return $client;
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json();
    }
}
