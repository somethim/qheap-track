<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Supplier;
use App\Rules\UniqueForUser;
use App\Rules\ValidContactEmail;
use App\Rules\ValidContactPhone;
use App\Utils\ContactVerificationUtils;
use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function rules(): array
    {
        $supplierId = $this->route('supplier')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                new UniqueForUser((new Supplier)->getTable(), 'name', $supplierId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:50', new ValidContactEmail],
            'contact_phone' => ['nullable', 'string', 'max:20', new ValidContactPhone],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('contact_email')) {
            $this->merge([
                'contact_email' => ContactVerificationUtils::cleanEmailAddress($this->input('contact_email')),
            ]);
        }
    }
}
