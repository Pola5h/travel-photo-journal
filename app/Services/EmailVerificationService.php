<?php

namespace App\Services;

use App\Exceptions\InvalidVerificationTokenException;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailVerificationService
{
    private const TOKEN_TTL_MINUTES = 60;

    public function issue(User $user): string
    {
        $user->emailVerificationTokens()->delete();

        $token = Str::random(64);

        $user->emailVerificationTokens()->create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
        ]);

        $user->notify(new VerifyEmailNotification($token));

        return $token;
    }

    public function verify(string $email, string $token): User
    {
        $user = User::query()->where('email', strtolower($email))->first();

        if ($user === null) {
            throw new InvalidVerificationTokenException;
        }

        $hash = hash('sha256', $token);

        $matched = $user->emailVerificationTokens()
            ->get()
            ->first(fn (EmailVerificationToken $candidate): bool => hash_equals($candidate->token_hash, $hash) && $candidate->expires_at->isFuture());

        if ($matched === null) {
            throw new InvalidVerificationTokenException;
        }

        DB::transaction(function () use ($user): void {
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->emailVerificationTokens()->delete();
        });

        return $user->refresh();
    }

    public function resend(string $email): void
    {
        $user = User::query()->where('email', strtolower($email))->first();

        if ($user === null || $user->email_verified_at !== null) {
            throw ValidationException::withMessages([
                'email' => ['No unverified account was found for this email address.'],
            ]);
        }

        $this->issue($user);
    }
}
