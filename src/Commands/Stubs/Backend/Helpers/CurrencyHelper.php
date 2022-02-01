<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class CurrencyHelper
{
    /**
     * Format currency from string to int
     * 
     * @param string|int $currency currency data in string ( ex: 12,000.00 )
     * 
     * @return float 
     */
    public function deformatCurrency($currency = null) {
        if (!$currency) {
            return 0;
        }
        $number = (float) str_replace(',', '', $currency);
        return round($number, 2);
    }
}
