<?php

use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

afterEach(fn () => cleanPhotoUploads());

it('creates a photo with an uploaded image', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);

    $response = $this->withToken($token)->post('/api/photos', [
        'image' => UploadedFile::fake()->image('sunset.jpg', 800, 600),
        'name' => 'Sunset over the bay',
        'location' => 'Goa, India',
        'description' => 'A warm evening on the beach.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Sunset over the bay')
        ->assertJsonPath('data.location', 'Goa, India')
        ->assertJsonPath('data.summary', null);

    $photo = Photo::firstOrFail();

    expect($photo->user_id)->toBe($user->id)
        ->and($photo->image_original_name)->toBe('sunset.jpg')
        ->and(file_exists(public_path($photo->image_path)))->toBeTrue();
});

it('lists only the users photos with pagination', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);

    Photo::factory()->for($user)->count(12)->create();

    $response = $this->withToken($token)->getJson('/api/photos');

    $response->assertOk()
        ->assertJsonCount(10, 'data.items')
        ->assertJsonPath('data.meta.total', 12);
});

it('shows a photo', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);
    $photo = Photo::factory()->for($user)->create();

    $this->withToken($token)->getJson('/api/photos/'.$photo->id)
        ->assertOk()
        ->assertJsonPath('data.id', $photo->id);
});

it('updates the text fields of a photo', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);
    $photo = Photo::factory()->for($user)->create();

    $this->withToken($token)->putJson('/api/photos/'.$photo->id, [
        'name' => 'Updated name',
        'location' => 'Updated location',
        'description' => 'Updated description.',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Updated name')
        ->assertJsonPath('data.location', 'Updated location');

    expect($photo->fresh()->description)->toBe('Updated description.');
});

it('deletes a photo and its file', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);
    $photo = Photo::factory()->for($user)->create();
    $path = $photo->image_path;

    touch(public_path($path));

    $this->withToken($token)->deleteJson('/api/photos/'.$photo->id)
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Photo::find($photo->id))->toBeNull()
        ->and(file_exists(public_path($path)))->toBeFalse();
});

it('rejects a bad mime type and an oversized image', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);

    $this->withToken($token)->post('/api/photos', [
        'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        'name' => 'Name',
        'location' => 'Location',
        'description' => 'Description.',
    ])->assertStatus(422)->assertJsonValidationErrors('image');

    $this->withToken($token)->post('/api/photos', [
        'image' => UploadedFile::fake()->image('big.jpg')->size(6000),
        'name' => 'Name',
        'location' => 'Location',
        'description' => 'Description.',
    ])->assertStatus(422)->assertJsonValidationErrors('image');
});

it('requires authentication and a verified email', function () {
    $unverified = User::factory()->unverified()->create();

    $this->getJson('/api/photos')->assertStatus(401);

    $token = JWTAuth::fromUser($unverified);

    $this->withToken($token)->getJson('/api/photos')->assertStatus(403);
});
