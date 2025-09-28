<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'avatar' => fake()->imageUrl(640, 480, 'people', true),
            'nickname' => fake()->unique()->userName(),
            'birthdate' => fake()->date(),
            'academic_level' => fake()->randomElement(['primary', 'secondary']),
            'gender' => fake()->randomElement(['male', 'female']),
            'user_id' => null,
        ];
    }
}
