<?php

use App\Http\Controllers\Orders\ClientController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Orders\ProductController;
use App\Http\Controllers\Orders\SupplierController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('orders', OrderController::class);

    Route::resource('products', ProductController::class);

    Route::get('clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::resource('clients', ClientController::class);

    Route::resource('suppliers', SupplierController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
