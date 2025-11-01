<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Order Notifier API",
 *     version="1.0.0",
 *     description="API for managing orders with notifications"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Local Development Server"
 * )
 *
 * @OA\Server(
 *     url="https://api.example.com/api/v1",
 *     description="Production Server"
 * )
 */
abstract class Controller extends BaseController
{
    //
}
