<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhilippinePhoneNumber implements ValidationRule
{
    public const MESSAGE = 'Use an 11-digit mobile number or a Philippine landline, such as 02-8123-4567.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        $value = trim((string) $value);
        if (!preg_match('/^[0-9+().\s-]+$/', $value)) {
            $fail(self::MESSAGE);
            return;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (preg_match('/^(?:09\d{9}|0[2-8]\d{7,9})$/', $digits) !== 1) {
            $fail(self::MESSAGE);
        }
    }
}
