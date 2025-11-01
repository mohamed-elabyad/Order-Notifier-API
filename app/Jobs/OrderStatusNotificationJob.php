<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderStatusNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(FcmService $fcm)
    {
        $order = Order::with('user.deviceTokens')->find($this->orderId);

        $tokens = $order->user->deviceTokens->pluck('token')->toArray();

        $notification = [
            'title' => "Order {$order->code} is {$order->status->value}",
            'body' => "Amount {$order->amount_decimal} - Updated at " . now()->format('H:i'),
        ];

        $data = [
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'order_code' => $order->code,
            'status' => $order->status->value,
        ];

        $fcm->sendMulticast($tokens, $notification, $data);
    }
}
