<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactor\VerifyTwoFactorRequest;
use App\Services\TwoFactorService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function setup(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $result = $twoFactor->setup($request->user());

        return ApiResponse::success($result, 'Scan the QR code with your authenticator app.');
    }

    public function verify(VerifyTwoFactorRequest $request, TwoFactorService $twoFactor): JsonResponse
    {
        $twoFactor->confirm($request->user(), $request->validated('otp'));

        return ApiResponse::success(null, 'Two-factor authentication enabled.');
    }
}
