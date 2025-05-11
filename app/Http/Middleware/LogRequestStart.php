<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogRequestStart
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('Request started', [
            'path' => $request->path(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'is_authenticated' => auth()->check(),
        ]);
        return $next($request);
    }
}