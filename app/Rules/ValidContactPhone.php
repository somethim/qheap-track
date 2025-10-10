<?php

namespace App\Rules;

use App\Utils\ContactVerificationUtils;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidContactPhone implements ValidationRule
{
    private ?string $defaultRegion;

    public function __construct(?string $defaultRegion = null)
    {
        $this->defaultRegion = $defaultRegion;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        if (!ContactVerificationUtils::validatePhoneNumber($value, $this->defaultRegion)) {
            $fail(__('validation.contact_phone_invalid'));
        }
    }
}

