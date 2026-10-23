<?php

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Services\EmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function issueTokenFor(User $user): string
{
    Notification::fake();

    return app(EmailVerificationService::class)->issue($user);
}

it('verifies an email with a valid token', function () {
    $user = User::factory()->unverified()->create();

    $token = issueTokenFor($user);

    $this->postJson('/api/auth/verify-email', ['email' => $user->email, 'token' => $token])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('rejects a wrong token', function () {
    $user = User::factory()->unverified()->create();

    issueTokenFor($user);

    $this->postJson('/api/auth/verify-email', ['email' => $user->email, 'token' => str_repeat('x', 64)])
        ->assertStatus(422);
});

it('rejects an expired token', function () {
    $user = User::factory()->unverified()->create();
    $token = str_repeat('a', 64);

    $user->emailVerificationTokens()->create([
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->subMinute(),
    ]);

    $this->postJson('/api/auth/verify-email', ['email' => $user->email, 'token' => $token])
        ->assertStatus(422);

    expect($user->fresh()->email_verified_at)->toBeNull();
});

it('rejects a reused token', function () {
    $user = User::factory()->unverified()->create();
    $token = issueTokenFor($user);

    $this->postJson('/api/auth/verify-email', ['email' => $user->email, 'token' => $token])->assertOk();
    $this->postJson('/api/auth/verify-email', ['email' => $user->email, 'token' => $token])->assertStatus(422);
});

it('resends a verification email', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->postJson('/api/auth/resend-verification', ['email' => $user->email])
        ->assertOk()
        ->assertJsonPath('success', true);

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('rejects resend for an unknown or verified account', function () {
    $verified = User::factory()->create();

    $this->postJson('/api/auth/resend-verification', ['email' => 'nobody@example.com'])
        ->assertStatus(422);

    $this->postJson('/api/auth/resend-verification', ['email' => $verified->email])
        ->assertStatus(422);
});
