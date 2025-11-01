<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::middleware('guest:sanctum')->group(function () {
        Route::post('auth/register', RegisterController::class);
        Route::post('auth/login', LoginController::class);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', LogoutController::class);
        Route::get('auth/me', MeController::class);


        Route::apiResource('orders', OrderController::class)
            ->except('destroy')
            ->middlewareFor('index', 'ability:orders:read')
            ->middlewareFor('store', 'ability:orders:write')
            ->middlewareFor('show', 'ability:orders:read')
            ->middlewareFor('update', 'ability:orders:write');

        Route::post('orders/{id}/notify', [NotificationController::class, 'notifyOrder'])
            ->middleware('ability:notify:send');


        Route::apiResource('devices', DeviceTokenController::class)
            ->only(['store', 'destroy'])
            ->middlewareFor('store', 'ability:devices:write')
            ->middlewareFor('destroy', 'ability:devices:write');
    });
});
