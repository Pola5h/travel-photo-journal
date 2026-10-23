<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('DEMO_USER_EMAIL');
        $password = env('DEMO_USER_PASSWORD');

        if (empty($email) || empty($password)) {
            $this->command?->warn('DEMO_USER_EMAIL / DEMO_USER_PASSWORD are not set; skipping demo user.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Demo User',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
    }
}
