<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Services\EmailVerificationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function verify(VerifyEmailRequest $request, EmailVerificationService $verification): JsonResponse
    {
        $user = $verification->verify($request->validated('email'), $request->validated('token'));

        return ApiResponse::success(new UserResource($user), 'Email verified. You can now log in.');
    }

    public function resend(ResendVerificationRequest $request, EmailVerificationService $verification): JsonResponse
    {
        $verification->resend($request->validated('email'));

        return ApiResponse::success(null, 'A new verification email has been sent.');
    }
}
