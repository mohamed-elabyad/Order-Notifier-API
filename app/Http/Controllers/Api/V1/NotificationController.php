<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NotificationController extends ApiBaseController
{
    use AuthorizesRequests;
    /**
     * @OA\Post(
     *     path="/orders/{id}/notify",
     *     tags={"Notifications"},
     *     summary="Manually trigger push notification",
     *     description="Send FCM push notification for order to all user devices. Requires notify:send ability.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notifications sent"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="success", type="integer", example=2),
     *                 @OA\Property(property="failed", type="integer", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="No device tokens found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - not your order or missing notify:send ability"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function notifyOrder($id, FcmService $fcm)
    {
        $order = Order::findOrFail($id);

        $this->authorize('notifier', $order);

        $tokens = $this->authUser->deviceTokens->pluck('token')->toArray();

        $notification = [
            'title' => "Order {$order->code} is {$order->status->value}",
            'body' => "Amount {$order->amount_decimal} - Status: {$order->status->value}",
        ];

        $data = [
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'order_code' => $order->code,
            'status' => $order->status->value,
        ];

        $result = $fcm->sendMulticast($tokens, $notification, $data);

        return response()->json([
            'message' => 'Notifications sent',
            'result' => $result
        ]);
    }
}
