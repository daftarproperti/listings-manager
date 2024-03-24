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
     * Safely cast to string. If parameter is not castable to string, this doesn't throw error but still render it
     * safely as a string "not stringable".
     */
    public static function castToString(mixed $value): string
    {
        if (is_scalar($value) || is_null($value)) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return "not stringable";
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

    /**
     * Asserts that a given value is a int.
     *
     * @param mixed $value The value to check.
     * @return int The int value, if the assertion passed.
     */
    public static function int($value): int
    {
        assert(is_int($value));
        return $value;
    }
}
