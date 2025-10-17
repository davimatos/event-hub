<?php

use App\Core\Http\Controllers\AuthController;
use App\Core\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/v1'], function () {

    Route::post('auth/token', [AuthController::class, 'login']);

    Route::post('users', [UserController::class, 'create']);

    Route::middleware('auth:sanctum')->group(function () {

    });
});
