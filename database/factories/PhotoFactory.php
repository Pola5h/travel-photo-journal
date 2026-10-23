<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'location' => fake()->city(),
            'description' => fake()->paragraph(),
            'summary' => null,
            'image_path' => 'uploads/photos/'.Str::uuid().'.jpg',
            'image_original_name' => fake()->word().'.jpg',
        ];
    }
}
