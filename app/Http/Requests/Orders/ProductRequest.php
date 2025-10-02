<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Product;
use App\Models\User;
use App\Rules\UniqueForUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                new UniqueForUser(Product::class),
            ],
            'price' => ['numeric', 'min:0'],
            'stock' => ['integer', 'min:0'],
            'sku' => [
                'required',
                'string',
                'max:12',
                new UniqueForUser(Product::class, 'sku'),
            ],
            'user_id' => ['required', 'exists:'.User::class.',id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
            'sku' => Str::upper(Str::random(3)).'-'.Str::random(8),
        ]);
    }
}
