<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:12'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->getName(),
            'sku' => $this->getSku(),
        ]);
    }

    public function getName(): ?string
    {
        $name = $this->query('name') ?: $this->input('name');

        return trim($name) ?: null;
    }

    public function getSku(): ?string
    {
        $sku = $this->query('sku') ?: $this->input('sku');

        return trim($sku) ?: null;
    }
}
