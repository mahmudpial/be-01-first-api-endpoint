<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/api/v1/');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'message',
                'status',
            ],
            'meta' => [
                'timestamp',
            ],
        ]);
    }

    public function test_greet_endpoint(): void
    {
        $response = $this->get('/api/v1/greet');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'name',
                'role',
                'bio',
                'skills',
            ],
        ]);
    }
}
