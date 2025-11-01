<?php

namespace Database\Factories;

use App\Enums\NotificationStatus;
use App\Models\DeviceToken;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationLog>
 */
class NotificationLogFactory extends Factory
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
            'order_id' => Order::factory(),
            'device_token_id' => fake()->boolean(80) ? DeviceToken::factory() : null,

            'payload' => [
                'title' => fake()->sentence(),
                'body' => fake()->paragraph(),
                'data' => [
                    'order_code' => strtoupper(fake()->unique()->bothify('ORD-#####')),
                    'extra' => fake()->word(),
                ],
            ],

            'response' => fake()->boolean(70)
                ? ['success' => true, 'message_id' => fake()->uuid()]
                : ['success' => false],
                
            'notification_status' => fake()->randomElement(NotificationStatus::values()),
            'send_at' => fake()->optional()->dateTimeBetween('-2 days', 'now'),


        ];
    }
}
