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
            'avatar' => null,
            'nickname' => fake()->unique()->userName(),
            'birthdate' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'daily_goal_minutes' => fake()->numberBetween(30, 120),
            'total_minutes' => fake()->numberBetween(0, 10000),
            'streak_days' => fake()->numberBetween(0, 30),
            'user_id' => null,
        ];
    }
}
