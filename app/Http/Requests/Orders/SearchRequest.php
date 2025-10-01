<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'term' => ['sometimes', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s@._-]+$/'],
        ];
    }

    public function getSearchTerm(): ?string
    {
        return trim($this->query('term')) ?: null;
    }
}
