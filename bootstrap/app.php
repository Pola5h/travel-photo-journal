<?php

use App\Exceptions\EmailNotVerifiedException;
use App\Exceptions\InvalidVerificationTokenException;
use App\Exceptions\TwoFactorRequiredException;
use App\Http\Middleware\EnsureEmailVerified;
use App\Http\Middleware\ForceJsonResponse;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'verified.email' => EnsureEmailVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof InvalidVerificationTokenException => ApiResponse::error($e->getMessage(), ['token' => [$e->getMessage()]], 422),
                $e instanceof EmailNotVerifiedException => ApiResponse::error($e->getMessage(), [], 403),
                $e instanceof TwoFactorRequiredException => ApiResponse::error($e->getMessage(), [], 401, ['two_factor_required' => true]),
                $e instanceof ValidationException => ApiResponse::error($e->getMessage(), $e->errors(), 422),
                $e instanceof AuthenticationException => ApiResponse::error('Unauthenticated.', [], 401),
                $e instanceof JWTException => ApiResponse::error('Token is invalid or expired.', [], 401),
                $e instanceof ThrottleRequestsException => ApiResponse::error('Too many attempts. Please try again later.', [], 429),
                $e instanceof NotFoundHttpException => ApiResponse::error('Resource not found.', [], 404),
                $e instanceof HttpExceptionInterface => ApiResponse::error($e->getMessage() ?: 'Request failed.', [], $e->getStatusCode()),
                default => ApiResponse::error('Server error.', [], 500),
            };
        });
    })->create();
