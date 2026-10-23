<?php

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('registers a user and sends a verification email', function () {
    Notification::fake();

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Alice',
        'email' => 'Alice@Example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'alice@example.com')
        ->assertJsonPath('data.email_verified_at', null);

    $user = User::where('email', 'alice@example.com')->firstOrFail();

    expect($user->email_verified_at)->toBeNull()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('secret-password');

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'alice@example.com']);

    $this->postJson('/api/auth/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('rejects a weak password', function () {
    $this->postJson('/api/auth/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertStatus(422)->assertJsonValidationErrors('password');
});
