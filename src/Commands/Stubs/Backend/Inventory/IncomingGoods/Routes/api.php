<?php

use __defaultNamespace__\Controllers\IncomingGoodsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('inventory/incoming-goods')->group(function () {
        Route::get('/', [IncomingGoodsController::class, 'index']);
        Route::post('/', [IncomingGoodsController::class, 'store']);
        Route::get('/{id}', [IncomingGoodsController::class, 'show']);
        Route::put('/{id}', [IncomingGoodsController::class, 'update']);
        Route::patch('/{id}/approval', [IncomingGoodsController::class, 'confirmApproval']);
        Route::delete('/{id}', [IncomingGoodsController::class, 'destroy']);
    });
});
