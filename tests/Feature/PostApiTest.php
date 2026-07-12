<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_posts(): void
    {
        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'posts' => [
                        '*' => ['id', 'title', 'content', 'author', 'created_at', 'updated_at'],
                    ],
                    'total',
                ],
                'meta',
            ]);
    }

    public function test_create_post(): void
    {
        $response = $this->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post for persistence.',
            'author' => 'Test Author',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'post' => ['id', 'title', 'content', 'author', 'created_at', 'updated_at'],
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'author' => 'Test Author',
        ]);
    }

    public function test_get_post_by_id(): void
    {
        $post = \App\Models\Post::create([
            'title' => 'Get Post Test',
            'content' => 'Test content',
            'author' => 'Test Author',
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.post.title', 'Get Post Test');
    }

    public function test_get_post_not_found(): void
    {
        $response = $this->getJson('/api/v1/posts/9999');

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_update_post(): void
    {
        $post = \App\Models\Post::create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'author' => 'Author',
        ]);

        $response = $this->putJson("/api/v1/posts/{$post->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.post.title', 'Updated Title');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_delete_post(): void
    {
        $post = \App\Models\Post::create([
            'title' => 'Delete Me',
            'content' => 'Content',
            'author' => 'Author',
        ]);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_get_posts_by_author(): void
    {
        \App\Models\Post::create(['title' => 'Post 1', 'content' => 'Content', 'author' => 'Alice']);
        \App\Models\Post::create(['title' => 'Post 2', 'content' => 'Content', 'author' => 'Alice']);
        \App\Models\Post::create(['title' => 'Post 3', 'content' => 'Content', 'author' => 'Bob']);

        $response = $this->getJson('/api/v1/posts/author/Alice');

        $response->assertStatus(200)
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.author', 'Alice');
    }

    public function test_validation_required_fields(): void
    {
        $response = $this->postJson('/api/v1/posts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }
}
