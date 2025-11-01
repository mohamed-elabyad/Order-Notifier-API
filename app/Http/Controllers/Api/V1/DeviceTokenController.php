<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\DeviceTokenRequest;
use App\Models\DeviceToken;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class DeviceTokenController extends ApiBaseController
{
    use AuthorizesRequests;
    /**
     * @OA\Post(
     *     path="/devices",
     *     tags={"Devices"},
     *     summary="Register device token",
     *     description="Save or update FCM device token. Deduplicates per user+token. Requires devices:write ability.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","platform"},
     *             @OA\Property(property="token", type="string", example="fcm_token_here_abc123xyz..."),
     *             @OA\Property(property="platform", type="string", enum={"android","ios","web"}, example="android")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Device token registered/updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="device_token", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="platform", type="string", example="android"),
     *                 @OA\Property(property="last_seen_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - missing devices:write ability"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(DeviceTokenRequest $request)
    {
        $validated = $request->validated();

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token' => $validated['token'],
            ],
            [
                'platform' => $validated['platform'],
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['device_token' => $deviceToken], 201);
    }

    /**
     * @OA\Delete(
     *     path="/devices/{id}",
     *     tags={"Devices"},
     *     summary="Remove device token",
     *     description="Delete a device token. User can only delete their own tokens. Requires devices:write ability.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Device token ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Device token deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Device token deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - not your token or missing devices:write ability"),
     *     @OA\Response(response=404, description="Device token not found")
     * )
     */
    public function destroy($id)
    {
        $deviceToken = DeviceToken::findOrFail($id);

        $this->authorize('delete-device-token', $deviceToken);

        $deviceToken->delete();

        return response()->json(['message' => 'Device token deleted successfully']);
    }
}
