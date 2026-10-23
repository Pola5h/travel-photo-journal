<?php

use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(fn () => cleanPhotoUploads());

it('returns 404 for another users photo on show, update and delete', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $photo = Photo::factory()->for($owner)->create();
    $token = apiTokenFor($intruder);

    $this->withToken($token)->getJson('/api/photos/'.$photo->id)->assertStatus(404);
    $this->withToken($token)->putJson('/api/photos/'.$photo->id, [
        'name' => 'Hijacked',
        'location' => 'Nowhere',
        'description' => 'Hijacked.',
    ])->assertStatus(404);
    $this->withToken($token)->deleteJson('/api/photos/'.$photo->id)->assertStatus(404);

    expect($photo->fresh()->name)->not->toBe('Hijacked');
});

it('does not list another users photos', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $photo = Photo::factory()->for($owner)->create();
    $token = apiTokenFor($intruder);

    $response = $this->withToken($token)->getJson('/api/photos');

    $response->assertOk()->assertJsonCount(0, 'data.items');

    expect($response->json('data.items'))->not->toContain($photo->id);
});
