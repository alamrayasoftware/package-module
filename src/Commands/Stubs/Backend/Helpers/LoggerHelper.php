<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class LoggerHelper
{
    /**
     * log error data
     * 
     * @param Throwable $throwable throwable data
     * @param int $companyId current company id
     * @param int $userId current user id
     * 
     */
    public function logError($throwable, $companyId = null, $userId = null)
    {
        $location = Route::currentRouteAction();
        $data = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'context' => $throwable->getMessage(),
            'location' => $location,
            'line' => $throwable->getLine()
        ];
        $this->privateLogger($data, $throwable->getCode());
    }

    /**
     * log success data
     * 
     * @param string $message message
     * @param int $companyId current company id
     * @param int $userId current user id
     * 
     */
    public function logSuccess($message, $companyId = null, $userId = null)
    {
        $location = Route::currentRouteAction();
        $data = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'context' => $message,
            'location' => $location,
            'line' => null
        ];
        $this->privateLogger($data, 200);
    }

    /**
     * log data into Illuminate\Support\Facades\Log
     * 
     * @param array $data logged data
     * @param string $errorCode error code : 200, 401, 403, etc
     * 
     */
    private function privateLogger($data, $errorCode)
    {
        switch (true) {
            case ($errorCode < 300):
                Log::info($data['context'], $data);
                break;

            case ($errorCode < 500):
                Log::warning($data['context'], $data);
                break;

            case ($errorCode >= 500):
                Log::error($data['context'], $data);
                break;
            
            default:
                Log::debug($data['context'], $data);
                break;
        }
    }
}