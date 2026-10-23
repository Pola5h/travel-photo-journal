<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

function loginToken(User $user): string
{
    return test()->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->json('data.access_token');
}

it('refreshes an access token and blacklists the old one', function () {
    $user = User::factory()->create(['password' => 'secret-password']);
    $token = loginToken($user);

    $response = $this->withToken($token)->postJson('/api/auth/refresh');

    $response->assertOk()->assertJsonPath('success', true);

    $newToken = $response->json('data.access_token');

    expect($newToken)->toBeString()->not->toBe($token);

    $this->app['auth']->forgetGuards();
    JWTAuth::unsetToken();

    $this->withToken($newToken)->postJson('/api/auth/logout')->assertOk();

    $this->app['auth']->forgetGuards();
    JWTAuth::unsetToken();

    $this->withToken($token)->postJson('/api/auth/logout')->assertStatus(401);
});

it('refreshes an expired access token inside the refresh window', function () {
    $user = User::factory()->create(['password' => 'secret-password']);
    $token = loginToken($user);

    $this->travel(16)->minutes();

    $this->withToken($token)->postJson('/api/auth/refresh')->assertOk();
});

it('rejects a refresh without a token', function () {
    $this->postJson('/api/auth/refresh')->assertStatus(401);
});

it('invalidates the token after logout', function () {
    $user = User::factory()->create(['password' => 'secret-password']);
    $token = loginToken($user);

    $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
    $this->withToken($token)->postJson('/api/auth/logout')->assertStatus(401);
});
