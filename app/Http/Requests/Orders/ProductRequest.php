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
        $productId = $this->route('product')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                new UniqueForUser((new Product)->getTable(), 'name', $productId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'sku' => [
                'required',
                'string',
                'max:12',
                new UniqueForUser((new Product)->getTable(), 'sku', $productId),
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
