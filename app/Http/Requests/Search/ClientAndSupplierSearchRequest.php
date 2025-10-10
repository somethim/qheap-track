<?php

namespace App\Http\Requests\Search;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ClientAndSupplierSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s@._-]*$/'],
        ];
    }

    public function getStartDate(): ?Carbon
    {
        return $this->query('start_date') ? Carbon::parse($this->query('start_date'))->startOfDay() : null;
    }

    public function getEndDate(): ?Carbon
    {
        return $this->query('end_date') ? Carbon::parse($this->query('end_date'))->endOfDay() : null;
    }

    public function getOrderType(): string
    {
        return $this->query('type', 'client');
    }

    public function getSortBy(): string
    {
        return $this->query('sort_by', 'created_at');
    }

    public function getSortDirection(): string
    {
        return $this->query('sort_direction', 'desc');
    }

    public function getSearchTerm(): ?string
    {
        return trim($this->query('search')) ?: null;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('search', $this->query('search'));
        $this->merge([
            'search' => is_string($raw) ? trim($raw) : null,
        ]);
    }

    private function validateDateRange($validator): void
    {
        $startDate = $this->query('start_date') ? Carbon::parse($this->query('start_date')) : null;
        $endDate = $this->query('end_date') ? Carbon::parse($this->query('end_date')) : null;

        if ($endDate && $endDate->isFuture()) {
            $validator->errors()->add('end_date', 'End date cannot be in the future.');
        }

        if ($startDate && $endDate && $startDate->greaterThan($endDate)) {
            $validator->errors()->add('date_range', 'Start date cannot be after end date.');
        }
    }
}
