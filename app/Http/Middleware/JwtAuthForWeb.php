<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthForWeb
{
    public function handle(Request $request, Closure $next): Response
    {
        // Debug log with type-safe query handling
        $query = $request->query();
        $queryData = is_array($query) ? $query : $query->all();
        \Log::debug('JwtAuthForWeb middleware triggered', [
            'url' => $request->url(),
            'query' => $queryData,
            'fullUrl' => $request->fullUrl()
        ]);

        try {
            $token = $request->query('token');

            if (!$token) {
                \Log::warning('No token provided in request', [
                    'url' => $request->url(),
                    'query' => $queryData,
                    'headers' => $request->headers->all()
                ]);
                return redirect()->route('login')->with('error', 'No token provided');
            }

            \Log::info('Attempting JWT authentication with token', ['token' => $token]);
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }

            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();

            if (!$user) {
                \Log::warning('JWT authentication failed: No user found', ['token' => $token]);
                return redirect()->route('login')->with('error', 'Invalid token');
            }

            \Log::info('JWT token authenticated for web route', ['user_id' => $user->id, 'token' => $token]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            return $next($request);
        } catch (JWTException $e) {
            \Log::error('JWT authentication failed', ['error' => $e->getMessage(), 'token' => $token]);
            return redirect()->route('login')->with('error', 'JWT authentication failed: ' . $e->getMessage());
        }
    }
}