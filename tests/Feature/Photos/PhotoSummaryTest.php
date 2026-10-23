<?php

use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.openrouter.key' => 'test-key',
        'services.openrouter.model' => 'test-model',
    ]);
});

afterEach(fn () => cleanPhotoUploads());

function summaryResponse(string $summary): array
{
    return ['choices' => [['message' => ['content' => $summary]]]];
}

function photoPayload(array $overrides = []): array
{
    return array_merge([
        'image' => UploadedFile::fake()->image('scene.jpg', 400, 300),
        'name' => 'A scene',
        'location' => 'Somewhere',
        'description' => 'We walked along the shore at sunset.',
    ], $overrides);
}

it('stores and returns an ai summary on create', function () {
    Http::fake(['openrouter.ai/*' => Http::response(summaryResponse('A calm walk by the shore. The sun set slowly.'), 200)]);

    $token = apiTokenFor(User::factory()->create());

    $response = $this->withToken($token)->post('/api/photos', photoPayload());

    $response->assertCreated()
        ->assertJsonPath('data.summary', 'A calm walk by the shore. The sun set slowly.');

    expect(Photo::firstOrFail()->summary)->toBe('A calm walk by the shore. The sun set slowly.');

    Http::assertSent(fn (Request $request) => $request['model'] === 'test-model'
        && $request['messages'][0]['role'] === 'system'
        && $request['messages'][1]['role'] === 'user');
});

it('keeps the photo when the ai request fails', function () {
    Http::fake(['openrouter.ai/*' => Http::response(['error' => 'nope'], 500)]);

    $token = apiTokenFor(User::factory()->create());

    $this->withToken($token)->post('/api/photos', photoPayload())
        ->assertCreated()
        ->assertJsonPath('data.summary', null);

    expect(Photo::count())->toBe(1);
});

it('keeps the photo when the ai request times out', function () {
    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    $token = apiTokenFor(User::factory()->create());

    $this->withToken($token)->post('/api/photos', photoPayload())
        ->assertCreated()
        ->assertJsonPath('data.summary', null);

    expect(Photo::count())->toBe(1);
});

it('regenerates the summary when the description changes', function () {
    Http::fakeSequence()
        ->push(summaryResponse('First summary.'), 200)
        ->push(summaryResponse('Second summary.'), 200);

    $user = User::factory()->create();
    $token = apiTokenFor($user);

    $photoId = $this->withToken($token)->post('/api/photos', photoPayload())
        ->assertCreated()
        ->assertJsonPath('data.summary', 'First summary.')
        ->json('data.id');

    $this->withToken($token)->putJson('/api/photos/'.$photoId, [
        'name' => 'A scene',
        'location' => 'Somewhere',
        'description' => 'A different description entirely.',
    ])->assertOk()->assertJsonPath('data.summary', 'Second summary.');

    expect(Photo::findOrFail($photoId)->summary)->toBe('Second summary.');

    $this->withToken($token)->putJson('/api/photos/'.$photoId, [
        'name' => 'A scene',
        'location' => 'Somewhere',
        'description' => 'A different description entirely.',
    ])->assertOk()->assertJsonPath('data.summary', 'Second summary.');

    Http::assertSentCount(2);
});

it('truncates a long description before sending it', function () {
    Http::fake(['openrouter.ai/*' => Http::response(summaryResponse('Summary.'), 200)]);

    $token = apiTokenFor(User::factory()->create());
    $longDescription = str_repeat('word ', 500);

    $this->withToken($token)->post('/api/photos', photoPayload(['description' => $longDescription]))
        ->assertCreated();

    Http::assertSent(fn (Request $request) => mb_strlen($request['messages'][1]['content']) <= 2000);
});
