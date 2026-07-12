<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * GET /api/v1/health
     * Health check endpoint with database status.
     */
    public function check(): JsonResponse
    {
        $status = 'healthy';
        $services = [];

        // Check database
        try {
            \DB::connection()->getPdo();
            $services['database'] = 'connected';
        } catch (\Exception $e) {
            $services['database'] = 'disconnected';
            $status = 'degraded';
        }

        $statusCode = $status === 'healthy' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'services' => $services,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ], $statusCode);
    }
}
