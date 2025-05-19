<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('ForceJsonResponse Middleware Executed', [
            'path' => $request->path(),
            'accept' => $request->header('accept'),
            'expects_json' => $request->expectsJson(),
        ]);

        if ($request->expectsJson()) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        Log::info('ForceJsonResponse Middleware Response', [
            'status' => $response->getStatusCode(),
            'content_type' => $response->headers->get('content-type'),
            'content' => substr($response->getContent(), 0, 200), // Limit content length for logging
        ]);

        // Only force JSON if the response is not a redirect and lacks a JSON content-type
        if (
            $request->expectsJson() &&
            !$response->isRedirection() &&
            (!$response->headers->get('content-type') || strpos($response->headers->get('content-type'), 'application/json') === false)
        ) {
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'error' => 'Response forced to JSON due to missing or incorrect content-type',
                'original_content' => substr($response->getContent(), 0, 200),
            ]));
        }

        return $response;
    }
}