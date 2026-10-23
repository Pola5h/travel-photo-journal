<?php

namespace App\Http\Middleware;

use App\Exceptions\EmailNotVerifiedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->email_verified_at === null) {
            throw new EmailNotVerifiedException;
        }

        return $next($request);
    }
}
