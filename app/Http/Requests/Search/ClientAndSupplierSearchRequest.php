<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class ClientAndSupplierSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'term' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s@._-]*$/'],
        ];
    }

    public function getSearchTerm(): ?string
    {
        $term = $this->validated('term');

        return $term && trim($term) !== '' ? trim($term) : null;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('term', $this->query('term'));
        $this->merge([
            'term' => is_string($raw) ? trim($raw) : null,
        ]);
    }
}
