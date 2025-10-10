<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Client;
use App\Models\Orders\Product;
use App\Models\Orders\Supplier;
use App\Models\User;
use App\Rules\ValidContactEmail;
use App\Rules\ValidContactPhone;
use App\Utils\ContactVerificationUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'client_id' => ['nullable', 'exists:'.Client::class.',id'],
            'supplier_id' => ['nullable', 'exists:'.Supplier::class.',id'],
            'order_products' => ['required', 'array', 'min:1'],
            'order_products.*.id' => ['required', 'integer', 'exists:'.Product::class.',id'],
            'order_products.*.price' => ['numeric', 'min:0'],
            'order_products.*.stock' => ['required', 'integer', 'min:1'],
            'user_id' => ['required', 'exists:'.User::class.',id'],
        ];

        $contactInfoRules = [
            'contact_info' => ['sometimes', 'array'],
            'contact_info.name' => ['nullable', 'string', 'max:255'],
            'contact_info.contact_email' => ['nullable', 'email', 'max:50', new ValidContactEmail],
            'contact_info.contact_phone' => ['nullable', 'string', 'max:20', new ValidContactPhone],
            'contact_info.address' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->input('client_id') && $this->input('supplier_id')) {
            throw ValidationException::withMessages([
                'order_type' => __('order.order_type.exclusive'),
            ]);
        }

        if (! $this->input('client_id') && ! $this->input('supplier_id')) {
            throw ValidationException::withMessages([
                'order_type' => __('order.order_type.required'),
            ]);
        }

        return array_merge($rules, $contactInfoRules);
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'user_id' => auth()->id(),
        ];

        if ($this->has('contact_info.contact_email')) {
            $data['contact_info'] = array_merge(
                $this->input('contact_info', []),
                ['contact_email' => ContactVerificationUtils::cleanEmailAddress($this->input('contact_info.contact_email'))]
            );
        }

        $this->merge($data);
    }
}
