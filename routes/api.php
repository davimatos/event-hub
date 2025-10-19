<?php

use App\Framework\Http\Controllers\AuthController;
use App\Framework\Http\Controllers\EventController;
use App\Framework\Http\Controllers\OrderController;
use App\Framework\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/v1'], function () {

    Route::post('auth/token', [AuthController::class, 'login']);

    Route::post('public/users', [UserController::class, 'create']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('users', [UserController::class, 'create']);

        Route::post('buy-ticket', [OrderController::class, 'create']);

        Route::middleware('user.organizer')->group(function () {

            Route::post('events', [EventController::class, 'create']);

        });

    });
});
