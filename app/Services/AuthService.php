<?php

namespace App\Services;

use App\Exceptions\EmailNotVerifiedException;
use App\Exceptions\TwoFactorRequiredException;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly EmailVerificationService $verification,
        private readonly TwoFactorService $twoFactor,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): User
    {
        $user = DB::transaction(fn (): User => User::create($data));

        $this->verification->issue($user);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $credentials
     * @return array<string, mixed>
     */
    public function attemptLogin(array $credentials): array
    {
        $user = User::query()->where('email', strtolower($credentials['email']))->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid email or password.');
        }

        if ($user->email_verified_at === null) {
            throw new EmailNotVerifiedException;
        }

        if ($user->two_factor_confirmed_at !== null) {
            $otp = $credentials['otp'] ?? null;

            if (! is_string($otp) || $otp === '') {
                throw new TwoFactorRequiredException;
            }

            if (! $this->twoFactor->verifyCode($user, $otp)) {
                throw ValidationException::withMessages([
                    'otp' => ['The one-time code is invalid.'],
                ]);
            }
        }

        return $this->tokenResponse($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        $guard = auth('api');

        $token = $guard->refresh();

        $user = $guard->setToken($token)->user();

        $guard->unsetToken();

        if ($user === null) {
            throw new AuthenticationException('Token is invalid or expired.');
        }

        return $this->tokenResponse($user, $token);
    }

    public function logout(): void
    {
        auth('api')->logout(true);
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenResponse(User $user, ?string $token = null): array
    {
        return [
            'access_token' => $token ?? auth('api')->login($user),
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
            'user' => new UserResource($user),
        ];
    }
}
