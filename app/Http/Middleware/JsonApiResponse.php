<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonApiResponse
{
    /**
     * Handle an incoming request and wrap errors in JSON structure.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->expectsJson() && !str_starts_with($request->path(), 'api/')) {
            return $response;
        }

        // Wrap successful responses
        if ($response->getStatusCode() < 400 && !isset($response->original['status'])) {
            $response->setContent(json_encode([
                'status' => 'success',
                'data' => json_decode($response->getContent(), true),
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'version' => 'v1',
                ],
            ]));
        }

        return $response;
    }
}
