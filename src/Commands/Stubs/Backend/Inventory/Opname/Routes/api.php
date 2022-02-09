<?php

use __defaultNamespace__\Controllers\OpnameController;
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
    Route::prefix('inventory/opname')->group(function () {
        Route::get('/', [OpnameController::class, 'index']);
        Route::post('/', [OpnameController::class, 'store']);
        Route::get('/{id}', [OpnameController::class, 'show']);
        Route::put('/{id}', [OpnameController::class, 'update']);
        Route::patch('/{id}/approval', [OpnameController::class, 'confirmApproval']);
        Route::delete('/{id}', [OpnameController::class, 'destroy']);
    });
});
