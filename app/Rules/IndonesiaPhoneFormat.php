<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IndonesiaPhoneFormat implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var string $phoneNumber */
        $phoneNumber = $value;

        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        if (!$phoneNumber) {
            $fail('Phone number can not be empty');
        }

        // Check if the number starts with 08 or 62 and has 10 to 13 digits
        /** @var string $phoneNumber */
        if (!preg_match('/^(?:08\d{8,11}|62\d{8,11}|1\d{8,11})$/', $phoneNumber)) {
            $fail('Phone number is not a valid Indonesian phone number');
        }
    }
}
