<?php

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a json 404 for unknown api routes', function () {
    $this->getJson('/api/does-not-exist')
        ->assertStatus(404)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['success', 'message', 'errors']);
});

it('returns validation errors in the api response format', function () {
    $this->postJson('/api/auth/register', [])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['success', 'message', 'errors' => ['name', 'email', 'password']]);
});

it('does not expose secrets in user resources', function () {
    $user = User::factory()->create();

    $payload = (new UserResource($user))->resolve();

    expect($payload)->not->toHaveKey('password')
        ->and($payload)->not->toHaveKey('two_factor_secret')
        ->and($payload)->toHaveKeys(['id', 'name', 'email', 'two_factor_enabled']);
});
