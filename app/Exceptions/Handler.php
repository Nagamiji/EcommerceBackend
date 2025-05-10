<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            // JWT Authentication exceptions (401 Unauthorized)
            if ($e instanceof JWTException ||
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException ||
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'details' => 'Please log in to access this resource.'
                ], 401);
            }

            // Model Not Found exception (404)
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => 'Not Found',
                    'details' => 'The resource you are looking for does not exist.'
                ], 404);
            }

            // Validation exception (422)
            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => 'Validation Error',
                    'details' => $e->errors()
                ], 422);
            }

            // Authentication exception (401)
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'details' => 'Authentication is required to access this resource.'
                ], 401);
            }

            // General Server Error (500)
            return response()->json([
                'error' => 'Server Error',
                'details' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred. Please try again later.'
            ], 500);
        }

        // For non-API requests, return the default behavior (views, etc.)
        return parent::render($request, $e);
    }
}
