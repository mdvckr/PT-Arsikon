<?php

if (! function_exists('format_quantity')) {
    /**
     * Format a quantity: if it's a whole number (e.g. 12.00), format without decimals (e.g. 12).
     * If user provided decimals, show up to 2 decimals without unnecessary trailing zeros (e.g. 12,5 or 14,25).
     *
     * @param mixed $value
     * @param int $maxDecimals
     * @return string
     */
    function format_quantity($value, int $maxDecimals = 2): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $floatVal = (float) $value;

        // If it is a whole number (e.g. 12.00 or 50.0)
        if (floor($floatVal) == $floatVal) {
            return number_format($floatVal, 0, ',', '.');
        }

        // If it has decimals (e.g. 12.5 or 0.75)
        $formatted = number_format($floatVal, $maxDecimals, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',');
    }
}
