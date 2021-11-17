<?php

namespace App\Helpers;

use Illuminate\Http\Response;

class ResponseFormatter
{
    /**
     * Format and return successful response data
     * 
     * @param string $message custom message
     * @param array $data associative array for extra data
     */
    public function successResponse($message = 'Data berhasil diproses', $data = [])
    {
        return $this->formatter(Response::HTTP_OK, $message, $data);
    }

    /**
     * Format and return error response data
     * 
     * @param exception|throwable $exception exception data
     * @param array $data associative array for extra data
     */
    public function errorResponse($exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        switch ($code) {
            case 400:
                return $this->formatter(Response::HTTP_BAD_REQUEST, $message);
                break;
            case 401:
                return $this->formatter(Response::HTTP_UNAUTHORIZED, $message);
                break;
            case 404:
                return $this->formatter(Response::HTTP_NOT_FOUND, $message);
                break;
            case 409:
                return $this->formatter(Response::HTTP_CONFLICT, $message);
                break;
            case 422:
                return $this->formatter(Response::HTTP_UNPROCESSABLE_ENTITY, $message);
                break;
            case 403:
                return $this->formatter(Response::HTTP_FORBIDDEN, $message);
                break;
            case 402:
                return $this->formatter(Response::HTTP_PAYMENT_REQUIRED, $message);
                break;
            default:
                return $this->formatter(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
                break;
        }
    }

    /** 
     * @param string|int $code http error code
     * @param string $message custom error message
     * @param array $data associative array for extra data
    */
    private function formatter($code, $message, $data = [])
    {
        return response()->json([
            'status' => $code == 200 ? 'success' : 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}