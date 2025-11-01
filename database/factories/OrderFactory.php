<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
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
            'code' => strtoupper(fake()->unique()->bothify('ORD-#####')),
            'amount_decimal' => fake()->randomFloat(2, 50, 5000),
            'status' => fake()->randomElement(OrderStatus::values()),
            'placed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now')
        ];
    }
}
