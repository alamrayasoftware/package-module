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
     * @param array $user current user
     * @param array $payload payload data
     * 
     */
    public function logError($throwable, $user = null, $payload = null)
    {
        $location = Route::currentRouteAction();
        $data = [
            'context' => $throwable->getMessage(),
            'location' => $location,
            'line' => $throwable->getLine(),
            'user' => $user,
            'payload' => $payload,
        ];
        $this->privateLogger($data, $throwable->getCode());
    }

    /**
     * log success data
     * 
     * @param string $message message
     * @param array $user current user
     * @param array $payload payload data
     * 
     */
    public function logSuccess($message, $user = null, $payload = null)
    {
        $location = Route::currentRouteAction();
        $data = [
            'context' => $message,
            'location' => $location,
            'user' => $user,
            'payload' => $payload,
        ];
        $this->privateLogger($data, 200);
    }

    /**
     * log debug data
     * 
     * @param array $user current user
     * @param array $payload payload data
     * 
     */
    public function logDebug($user = null, $payload = null)
    {
        $location = Route::currentRouteAction();
        $data = [
            'location' => $location,
            'user' => $user,
            'payload' => $payload,
        ];
        $this->privateLogger($data, 600);
    }

    /**
     * log data into Illuminate\Support\Facades\Log
     * 
     * @param array $data logged data
     * @param string $errorCode error code : 200, 401, 403, etc
     * 
     */
    private function privateLogger($data, $errorCode = false)
    {
        switch (true) {
            case (($errorCode == false) || ($errorCode >= 500) || is_string($errorCode)):
                Log::error($data['context'], $data);
                break;

            case ($errorCode < 300):
                Log::info($data['context'], $data);
                break;

            case ($errorCode < 500):
                Log::warning($data['context'], $data);
                break;

            default:
                Log::debug($data['context'], $data);
                break;
        }
    }
}