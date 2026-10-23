<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,60');
    Route::post('verify-email', [EmailVerificationController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('resend-verification', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1');
});
