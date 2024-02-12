<?php

namespace App\Helpers;

class Assert
{
    /**
     * Asserts that a given value is a string.
     *
     * @param mixed $value The value to check.
     * @return string The string value, if the assertion passed.
     */
    public static function string($value): string
    {
        assert(is_string($value));
        return $value ? $value : '';
    }
}
