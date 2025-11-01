<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class RegisterController extends ApiBaseController
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Auth"},
     *     summary="Register new user and return token",
     *     description="Creates a new user account and returns Sanctum token with specified abilities",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","device_name"},
     *             @OA\Property(property="name", type="string", example="Ahmed Ali"),
     *             @OA\Property(property="email", type="string", format="email", example="ahmed@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6, example="secret123"),
     *             @OA\Property(property="device_name", type="string", example="postman"),
     *             @OA\Property(
     *                 property="abilities",
     *                 type="array",
     *                 @OA\Items(type="string", enum={"orders:read","orders:write","notify:send","devices:write"}),
     *                 example={"orders:read","orders:write","notify:send","devices:write"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="1|abc123xyz..."),
     *             @OA\Property(property="abilities", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function __invoke(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken($validated['device_name'], $validated['abilities'])->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'abilities' => $validated['abilities'],
        ], 201);
    }
}
