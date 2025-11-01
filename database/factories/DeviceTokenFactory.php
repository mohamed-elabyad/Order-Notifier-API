<?php

namespace Database\Factories;

use App\Enums\DevicePlatform;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Validation\Rules\Unique;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceToken>
 */
class DeviceTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token' => 'fake_token_' . $this->faker->unique()->uuid(),
            'platform' => $this->faker->randomElement(['android', 'ios', 'web']),
            'last_seen_at' => now(),
        ];
    }
}
