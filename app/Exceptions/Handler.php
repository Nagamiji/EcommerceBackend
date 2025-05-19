<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        $isApiRequest = $request->is('api/*');
        Log::info('Exception Handler Render', [
            'path' => $request->path(),
            'is_api' => $isApiRequest,
            'expects_json' => $request->expectsJson(),
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'request_headers' => $request->headers->all()
        ]);

        if ($isApiRequest) {
            $request->headers->set('Accept', 'application/json');
            $request->setJson(new \Symfony\Component\HttpFoundation\ParameterBag($request->json()->all()));
        }

        if ($request->expectsJson() || $isApiRequest) {
            if ($e instanceof JWTException ||
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException ||
                $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'details' => 'Please log in to access this resource.'
                ], 401);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => 'Not Found',
                    'details' => 'The resource you are looking for does not exist.'
                ], 404);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => 'Validation Error',
                    'details' => $e->errors()
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'details' => 'Authentication is required to access this resource.'
                ], 401);
            }

            return response()->json([
                'error' => 'Server Error',
                'details' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred. Please try again later.'
            ], 500);
        }

        return parent::render($request, $e);
    }

    public function renderForConsole($output, Throwable $e)
    {
        Log::error('Console Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        parent::renderForConsole($output, $e);
    }
}