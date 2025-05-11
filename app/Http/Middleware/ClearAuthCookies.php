<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClearAuthCookies
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('Clearing auth cookies for request', ['path' => $request->path(), 'session_id' => $request->session()->getId()]);

        if ($request->path() === 'login') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $next($request)->withCookie(cookie()->forget('laravel_session'));
    }
}