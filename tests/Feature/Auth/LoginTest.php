<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in a verified user and returns a jwt', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonPath('data.expires_in', 900)
        ->assertJsonStructure([
            'data' => ['access_token', 'user' => ['id', 'name', 'email']],
        ]);

    expect($response->json('data.access_token'))->toBeString()->not->toBeEmpty();
});

it('rejects a wrong password without revealing which field is wrong', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $wrongPassword = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $unknownEmail = $this->postJson('/api/auth/login', [
        'email' => 'nobody@example.com',
        'password' => 'wrong-password',
    ]);

    $wrongPassword->assertStatus(401);
    $unknownEmail->assertStatus(401);

    expect($wrongPassword->json('message'))->toBe($unknownEmail->json('message'));
});

it('rejects an unverified user', function () {
    $user = User::factory()->unverified()->create(['password' => 'secret-password']);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertStatus(403)->assertJsonPath('success', false);
});

it('rate limits login attempts per email and ip', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});
