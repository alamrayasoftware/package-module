<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class LoggerHelper
{
    /**
     * Format and return successful response data
     * 
     * @param Throwable $throwable throwable data
     * @param int $companyId current company id
     * @param int $userId current user id
     * 
     */
    public function logError($throwable, $companyId = null, $userId = null)
    {
        $location = Route::currentRouteAction();
        Log::error($location, [
            'company_id' => $companyId,
            'user_id' => $userId,
            'context' => $throwable->getMessage(),
            'line' => $throwable->getLine()
        ]);
    }
}