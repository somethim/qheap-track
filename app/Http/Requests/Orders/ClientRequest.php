<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Client;
use App\Rules\UniqueForUser;
use App\Rules\ValidContactEmail;
use App\Rules\ValidContactPhone;
use App\Utils\ContactVerificationUtils;
use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                new UniqueForUser((new Client)->getTable(), 'name', $clientId),
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
