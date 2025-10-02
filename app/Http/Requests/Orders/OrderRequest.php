<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Client;
use App\Models\Orders\Product;
use App\Models\Orders\Supplier;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'client_id' => ['exists:'.Client::class.',id'],
            'supplier_id' => ['exists:'.Supplier::create().',id'],
            'order_products' => ['required', 'array', 'min:1'],
            'order_products.*.id' => ['required', 'integer', 'exists:'.Product::class.',id'],
            'order_products.*.price' => ['numeric', 'min:0'],
            'order_products.*.stock' => ['required', 'integer', 'min:1'],
            'user_id' => ['required', 'exists:'.User::class.',id'],
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}
