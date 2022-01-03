<?php

if (!function_exists('deformatCurrency')) {

    /**
     * Format currency from string to int
     * 
     * @param string $currency currency data in string ( ex: 12,000.00 )
     * 
     * @return float 
     */
    function deformatCurrency(
        string $currency = null
    ) {
        if (!$currency) {
            return 0;
        }
        $number = (float) str_replace(',', '', $currency);
        return round($number, 2);
    }
}
