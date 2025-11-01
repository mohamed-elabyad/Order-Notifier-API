<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

abstract class ApiBaseController extends Controller
{
    public ?User $authUser;

    public function __construct()
    {
        $this->middleware(function (Request $request, Closure $next) {
            /** @var User|null $user */
            $user = $request->user('sanctum');

            $this->authUser = $user;

            return $next($request);
        });
    }
}
