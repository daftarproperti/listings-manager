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

    /**
     * Asserts that a given value is a boolean.
     *
     * @param mixed $value The value to check.
     * @return bool The boolean value, if the assertion passed.
     */
    public static function boolean($value): bool
    {
        assert(is_bool($value));
        return (bool)$value;
    }
}
