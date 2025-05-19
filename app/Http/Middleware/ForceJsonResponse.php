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
            'expects_json' => $request->expectsJson()
        ]);

        $request->headers->set('Accept', 'application/json');
        
        $response = $next($request);

        Log::info('ForceJsonResponse Middleware Response', [
            'status' => $response->getStatusCode(),
            'content_type' => $response->headers->get('content-type'),
            'content' => $response->getContent()
        ]);

        if (!$response->headers->get('content-type') || strpos($response->headers->get('content-type'), 'application/json') === false) {
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode(['error' => 'Response forced to JSON due to missing content-type']));
        }

        return $response;
    }
}