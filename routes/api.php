<?php

use App\Core\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/v1'], function () {

    Route::post('auth/token', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

    });
});
