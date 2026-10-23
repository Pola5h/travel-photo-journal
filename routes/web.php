<?php

use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/photos');

Route::get('/register', [PageController::class, 'register']);
Route::get('/login', [PageController::class, 'login']);
Route::get('/check-your-email', [PageController::class, 'checkYourEmail']);
Route::get('/verify-email', [PageController::class, 'verifyEmail']);

Route::get('/photos', [PageController::class, 'photos']);
Route::get('/photos/create', [PageController::class, 'createPhoto']);
Route::get('/photos/{id}/edit', [PageController::class, 'editPhoto'])->whereNumber('id');

Route::get('/settings/security', [PageController::class, 'security']);
