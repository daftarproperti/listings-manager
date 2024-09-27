<?php

namespace App\Helpers;

class PhoneNumber
{
    public static function canonicalize(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        ;

        if (!$phoneNumber) {
            return '';
        }

        if (str_starts_with($phoneNumber, '8')) {
            $phoneNumber = '62' . $phoneNumber;
        }

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '62' . substr($phoneNumber, 1);
        }

        return '+' . $phoneNumber;
    }
}
