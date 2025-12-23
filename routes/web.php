<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

//Route::middleware('api')->prefix('api')->group(function () {
//    Route::prefix('auth')->group(function () {
//        Route::post('/register', [AuthController::class, 'register']);
//        Route::post('/login', [AuthController::class, 'login']);
//    });
//
//    Route::middleware('auth:sanctum')->group(function () {
//        Route::post('auth/logout', [AuthController::class, 'logout']);
//
//        Route::apiResource('reports', ReportController::class);
//    });
//});
