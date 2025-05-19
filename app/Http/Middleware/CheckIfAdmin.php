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
            \Log::warning('CheckIfAdmin middleware failed', ['user_id' => auth()->id() ?? 'none']);
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        \Log::info('CheckIfAdmin middleware passed', ['user_id' => auth()->id()]);
        return $next($request);
    }
}