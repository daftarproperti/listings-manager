<?php

namespace App\Helpers;

class NumFormatter
{
    public static function numberFormatShort(float $n, int $precision = 1): string
    {
        if ($n < 900) {
            // 0 - 900
            $divisor = 1;
            $suffix = '';
        } else if ($n < 900000) {
            // 0.9k-850k
            $divisor = 1000;
            $suffix = __('compact.thousand',);
        } else if ($n < 900000000) {
            // 0.9m-850m
            $divisor = 1000000;
            $suffix = __('compact.million');
        } else if ($n < 900000000000) {
            // 0.9b-850b
            $divisor = 1000000000;
            $suffix = __('compact.billion');
        } else {
            // 0.9t+
            $divisor = 1000000000000;
            $suffix = __('compact.trillion');
        }

        $formattedValue = number_format($n / $divisor, $precision, ',', '.');

        // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ($precision > 0) {
            $dotzero = ',' . str_repeat('0', $precision);
            $formattedValue = str_replace($dotzero, '', $formattedValue);
        }

        $currencySymbol = __('currency.symbol');

        return  sprintf('%s %s%s', $currencySymbol, $formattedValue, $suffix);
    }
}
