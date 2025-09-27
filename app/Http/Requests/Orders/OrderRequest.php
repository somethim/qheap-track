<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Client;
use App\Models\Orders\Product;
use App\Models\Orders\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'client_id' => ['exists:'.Client::class.',id'],
            'supplier_id' => ['exists:'.Supplier::create().',id'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'integer', 'exists:'.Product::class.',id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['numeric', 'min:0'],
        ];

        if ($this->has('client_id') && $this->has('supplier_id')) {
            throw ValidationException::withMessages([
                'order_type' => __('order.order_type.exclusive'),
            ]);
        }

        if (! $this->has('client_id') && ! $this->has('supplier_id')) {
            throw ValidationException::withMessages([
                'order_type' => __('order.order_type.required'),
            ]);
        }

        return $rules;
    }
}
