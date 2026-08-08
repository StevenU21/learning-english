<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
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
            'avatar' => null,
            'nickname' => fake()->unique()->userName(),
            'birthdate' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'daily_goal_minutes' => fake()->numberBetween(30, 120),
            'total_minutes' => fake()->numberBetween(0, 10000),
            'user_id' => null,
        ];
    }
}
