<?php

namespace App\Helpers;

class Cast
{
    /**
     * Safely cast to string. If parameter is not castable to string, this doesn't throw error but still render it
     * safely as a string "not stringable".
     */
    public static function toString(mixed $value): string
    {
        try {
            return strval($value); // @phpstan-ignore-line we are safely catching the error.
        } catch (\Error) {
            return 'not stringable';
        }
    }
}
