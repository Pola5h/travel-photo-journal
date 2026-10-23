<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

/**
 * @return array{secret: string, token: string}
 */
function enableTwoFactor(User $user): array
{
    $token = apiTokenFor($user);

    $secret = test()->withToken($token)
        ->postJson('/api/auth/2fa/setup')
        ->json('data.secret');

    test()->withToken($token)->postJson('/api/auth/2fa/verify', [
        'otp' => app(Google2FA::class)->getCurrentOtp($secret),
    ])->assertOk();

    return ['secret' => $secret, 'token' => $token];
}

it('returns a secret and qr code on setup', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);

    $response = $this->withToken($token)->postJson('/api/auth/2fa/setup');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['secret', 'qr_svg']]);

    expect($response->json('data.secret'))->toBeString()->not->toBeEmpty()
        ->and($response->json('data.qr_svg'))->toContain('<svg')
        ->and($user->fresh()->two_factor_secret)->toBe($response->json('data.secret'))
        ->and($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('rejects a wrong code and accepts the right one', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);

    $secret = $this->withToken($token)->postJson('/api/auth/2fa/setup')->json('data.secret');
    $valid = app(Google2FA::class)->getCurrentOtp($secret);
    $invalid = str_pad((string) (((int) $valid + 1) % 1000000), 6, '0', STR_PAD_LEFT);

    $this->withToken($token)->postJson('/api/auth/2fa/verify', ['otp' => $invalid])
        ->assertStatus(422)
        ->assertJsonValidationErrors('otp');

    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();

    $this->withToken($token)->postJson('/api/auth/2fa/verify', ['otp' => $valid])->assertOk();

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

it('refuses to set up 2fa twice', function () {
    $user = User::factory()->create();
    ['secret' => $secret, 'token' => $token] = enableTwoFactor($user);

    $this->withToken($token)->postJson('/api/auth/2fa/setup')
        ->assertStatus(409);

    expect($user->fresh()->two_factor_secret)->toBe($secret);
});

it('requires an otp at login once 2fa is active', function () {
    $user = User::factory()->create();
    ['secret' => $secret] = enableTwoFactor($user);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertStatus(401)
        ->assertJsonPath('two_factor_required', true);

    $valid = app(Google2FA::class)->getCurrentOtp($secret);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'otp' => str_pad((string) (((int) $valid + 1) % 1000000), 6, '0', STR_PAD_LEFT),
    ])->assertStatus(422)->assertJsonValidationErrors('otp');

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'otp' => app(Google2FA::class)->getCurrentOtp($secret),
    ])->assertOk()->assertJsonStructure(['data' => ['access_token']]);
});
