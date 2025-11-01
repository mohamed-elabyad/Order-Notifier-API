<?php

namespace Database\Seeders;

use App\Models\DeviceToken;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $users->each(function ($user) {
            $orders = Order::where('user_id', $user->id)->get();

            $devices = DeviceToken::where('user_id', $user->id)->get();

            NotificationLog::factory(fake()->numberBetween(2, 6))->create([
                'user_id' => $user->id,
                'order_id' => $orders->random()->id ?? null,
                'device_token_id' => $devices->count() ? $devices->random()->id : null,
            ]);
        });
    }
}
