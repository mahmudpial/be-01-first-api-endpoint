<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    public function test_welcome_endpoint_returns_success_response(): void
    {
        $response = $this->getJson('/api/v1/');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'message',
                    'status',
                    'timestamp',
                ],
                'meta' => [
                    'timestamp',
                    'version',
                ],
            ])
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'online');
    }

    public function test_greet_endpoint_returns_developer_info(): void
    {
        $response = $this->getJson('/api/v1/greet');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'name',
                    'role',
                    'bio',
                    'skills' => [
                        'languages',
                        'frameworks',
                        'backend',
                        'database',
                        'integration',
                        'tools',
                        'deployment',
                    ],
                    'currently_learning',
                ],
                'meta',
            ])
            ->assertJsonPath('data.name', 'Pial Mahmud')
            ->assertJsonPath('data.role', 'Backend Intern');
    }

    public function test_health_endpoint_checks_database_connection(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'database',
            ]);
    }

    public function test_invalid_route_returns_404(): void
    {
        $response = $this->getJson('/api/v1/nonexistent');

        $response->assertStatus(404);
    }
}
