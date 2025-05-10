<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckIfAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            \Log::info('Token parsed in admin middleware', ['user_id' => $user->id, 'token' => $request->header('Authorization')]);
            if ($user && $user->is_admin) {
                \Log::info('User is admin', ['user_id' => $user->id]);
                return $next($request);
            }
            \Log::warning('User is not admin', ['user_id' => $user ? $user->id : 'unknown']);
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            \Log::error('JWT authentication failed in admin middleware', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}