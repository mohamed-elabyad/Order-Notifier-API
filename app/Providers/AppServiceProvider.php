<?php

namespace App\Providers;

use App\Models\DeviceToken;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('delete-device-token', function (User $user, DeviceToken $deviceToken){
            return $user->id === $deviceToken->user_id;
        });

        Gate::define('notifier', function (User $user,  Order $order){
            return $order->user_id === $user->id;
        });
    }
}
