<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::post('/verify-email', [EmailVerificationController::class, 'verify'])->name('verification.verify');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::post('email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::put('/{id}', [UserController::class, 'update']);
    });

    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('/{id}', [ReportController::class, 'show']);
        Route::put('/{id}', [ReportController::class, 'update']);
        Route::delete('/{id}', [ReportController::class, 'destroy']);
    });

    Route::prefix('role')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
        Route::post('/assign', [RoleController::class, 'assignRole']);
    });

    Route::get('dashboard', [DashboardController::class, 'index']);
});
