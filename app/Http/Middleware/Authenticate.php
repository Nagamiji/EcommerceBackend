<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // Avoid redirect for API requests (do not redirect to 'login' route for API requests)
        if ($request->expectsJson()) {
            return null; // Do not redirect, just throw the exception
        }

        // For non-API requests, you can define a login route if needed
        return route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        // For API requests, return a JSON response indicating unauthenticated status
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthenticated',
                'details' => 'Please log in to access this resource.'
            ], 401);
        }

        // For non-API requests, proceed with default behavior (redirect to login page)
        parent::unauthenticated($request, $guards);
    }
}
