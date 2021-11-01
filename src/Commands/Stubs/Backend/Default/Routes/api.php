<?php

use __defaultNamespace__\Controllers\__childModuleName__Controller;
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


Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('__parentModuleName__/__childModuleName__')->group(function () {
        Route::get('/', [__childModuleName__Controller::class, 'index']);
        Route::post('/', [__childModuleName__Controller::class, 'store']);
        Route::get('/{id}', [__childModuleName__Controller::class, 'show']);
        Route::put('/{id}', [__childModuleName__Controller::class, 'update']);
        Route::delete('/{id}', [__childModuleName__Controller::class, 'destroy']);
    });
});
