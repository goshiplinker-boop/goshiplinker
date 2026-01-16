<?php

if (!function_exists('default_pagination_limit')) {
    function default_pagination_limit()
    {
        return config('app.pagination_limit', 100); // Default to 20 items per page
    }
}

if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol($currencyCode)
    {
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => 'Rs.',
            // Add more currencies here
        ];
        return $currencySymbols[$currencyCode] ?? $currencyCode;
    }
}
if (! function_exists('panelPrefix')) {
    function panelPrefix()
    {
        return request()->segment(1);
    }
}

?>