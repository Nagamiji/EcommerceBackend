<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIfAdmin
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('CheckIfAdmin middleware: User authenticated: ' . (auth()->check() ? 'yes' : 'no') . ', is_admin: ' . (auth()->check() && auth()->user()->is_admin ? 'true' : 'false'));

        if (!auth()->check() || !auth()->user()->is_admin) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}