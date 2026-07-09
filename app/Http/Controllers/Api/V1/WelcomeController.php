<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WelcomeController extends Controller
{
    /**
     * GET /api/v1/
     * Simple root endpoint confirming the API is alive.
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'message' => 'Welcome to my first API endpoint 🚀',
            'status' => 'online',
        ]);
    }

    /**
     * GET /api/v1/greet
     * Returns basic info about the developer / project, including a
     * categorized breakdown of the current skill stack.
     */
    public function greet(): JsonResponse
    {
        return $this->success([
            'name' => 'Pial Mahmud',
            'role' => 'Backend Intern',
            'bio' => 'Full-stack engineer focused on Laravel backends and Vue.js frontends.',
            'skills' => [
                'languages' => ['JavaScript', 'PHP'],
                'frameworks' => ['Laravel', 'Vue.js'],
                'backend' => [
                    'REST API design',
                    'JWT authentication',
                    'Authentication systems',
                    'ORM (Eloquent)',
                    'Repository pattern',
                    'OOP',
                    'Data Structures & Algorithms',
                ],
                'database' => ['MySQL', 'Database design'],
                'integration' => ['Third-party API integration', 'AI integration'],
                'tools' => ['Git & GitHub', 'Postman', 'Docker'],
                'deployment' => ['Vercel', 'Render'],
            ],
            'currently_learning' => 'Backend AI Engineering',
        ]);
    }

    /**
     * Standard success envelope used across all API responses.
     */
    private function success(array $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ], $status);
    }
}