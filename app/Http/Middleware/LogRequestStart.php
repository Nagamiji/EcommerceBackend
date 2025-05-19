<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestStart
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('LogRequestStart Middleware Executed', [
            'path' => $request->path(),
            'headers' => $request->headers->all(),
            'isApi' => $request->is('api/*'),
            'auth' => $request->header('authorization'),
            'middleware' => $request->route()->middleware() ?? 'none' // Added to check assigned middleware
        ]);
        
        return $next($request);
    }
}