<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,60');
    Route::post('verify-email', [EmailVerificationController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('resend-verification', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('throttle:30,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

Route::middleware(['auth:api', 'verified.email'])->group(function () {
    Route::post('auth/2fa/setup', [TwoFactorController::class, 'setup']);
    Route::post('auth/2fa/verify', [TwoFactorController::class, 'verify'])->middleware('throttle:5,1');

    Route::get('photos', [PhotoController::class, 'index']);
    Route::post('photos', [PhotoController::class, 'store']);
    Route::get('photos/{id}', [PhotoController::class, 'show'])->whereNumber('id');
    Route::put('photos/{id}', [PhotoController::class, 'update'])->whereNumber('id');
    Route::delete('photos/{id}', [PhotoController::class, 'destroy'])->whereNumber('id');
});
