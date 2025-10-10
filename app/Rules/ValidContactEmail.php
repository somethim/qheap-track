<?php

namespace App\Rules;

use App\Utils\ContactVerificationUtils;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidContactEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        if (! ContactVerificationUtils::validateEmailAddress($value)) {
            $fail(__('validation.contact_email_invalid'));
        }
    }
}
