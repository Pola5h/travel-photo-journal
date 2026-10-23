<?php

use App\Models\User;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function apiTokenFor(User $user, string $password = 'password'): string
{
    return test()->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => $password,
    ])->json('data.access_token');
}

function cleanPhotoUploads(): void
{
    foreach (glob(public_path('uploads/photos/*')) ?: [] as $file) {
        if (is_file($file) && ! in_array(basename($file), ['.gitkeep', '.htaccess'], true)) {
            unlink($file);
        }
    }
}
