<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        Log::info('Authenticate middleware: Determining redirect', [
            'path' => $request->path(),
            'method' => $request->method(),
            'is_authenticated' => auth()->check(),
            'expects_json' => $request->expectsJson(),
            'session_id' => $request->session()->getId(),
        ]);

        // Avoid redirect for API requests
        if ($request->expectsJson()) {
            Log::info('Authenticate middleware: API request, no redirect', [
                'session_id' => $request->session()->getId(),
            ]);
            return null; // Do not redirect, just throw the exception
        }

        // For non-API requests, redirect to login
        Log::info('Authenticate middleware: Redirecting to login', [
            'redirecting_to' => route('login'),
            'session_id' => $request->session()->getId(),
        ]);
        return route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        Log::info('Authenticate middleware: Handling unauthenticated request', [
            'path' => $request->path(),
            'guards' => $guards,
            'expects_json' => $request->expectsJson(),
            'session_id' => $request->session()->getId(),
        ]);

        // For API requests, return a JSON response
        if ($request->expectsJson()) {
            Log::info('Authenticate middleware: Returning JSON error for API request', [
                'session_id' => $request->session()->getId(),
            ]);
            return response()->json([
                'error' => 'Unauthenticated',
                'details' => 'Please log in to access this resource.'
            ], 401);
        }

        // For non-API requests, proceed with default behavior (redirect to login)
        Log::info('Authenticate middleware: Proceeding with default unauthenticated behavior', [
            'session_id' => $request->session()->getId(),
        ]);
        parent::unauthenticated($request, $guards);
    }
}