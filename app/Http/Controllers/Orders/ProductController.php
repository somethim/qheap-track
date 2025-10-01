<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ProductRequest;

class ProductController extends Controller
{
    public function index() {}

    public function create() {}

    public function store(ProductRequest $request) {}

    public function show(string $id) {}

    public function update(ProductRequest $request, string $id) {}

    public function destroy(string $id) {}
}
