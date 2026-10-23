<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra), $status);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     * @param  array<string, mixed>  $extra
     */
    public static function error(string $message, array $errors = [], int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $extra), $status);
    }
}
