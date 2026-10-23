<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $auth): JsonResponse
    {
        $user = $auth->register($request->validated());

        return ApiResponse::success(
            new UserResource($user),
            'Registered. Please check your email to verify your account.',
            201,
        );
    }

    public function login(LoginRequest $request, AuthService $auth): JsonResponse
    {
        $result = $auth->attemptLogin($request->validated());

        return ApiResponse::success($result, 'Logged in.');
    }

    public function refresh(AuthService $auth): JsonResponse
    {
        $result = $auth->refresh();

        return ApiResponse::success($result, 'Token refreshed.');
    }

    public function logout(AuthService $auth): JsonResponse
    {
        $auth->logout();

        return ApiResponse::success(null, 'Logged out.');
    }
}
