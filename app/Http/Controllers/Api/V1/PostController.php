<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contracts\PostRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    /**
     * GET /api/v1/posts
     * List all posts.
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'posts' => $this->postRepository->all(),
            'total' => $this->postRepository->count(),
        ]);
    }

    /**
     * GET /api/v1/posts/{id}
     * Get a single post by ID.
     */
    public function show(int $id): JsonResponse
    {
        $post = $this->postRepository->getById($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        return $this->success(['post' => $post]);
    }

    /**
     * POST /api/v1/posts
     * Create a new post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
        ]);

        $post = $this->postRepository->create($validated);
        return $this->success(['post' => $post], 201);
    }

    /**
     * PUT /api/v1/posts/{id}
     * Update an existing post.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $post = $this->postRepository->getById($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'author' => 'nullable|string|max:255',
        ]);

        $updated = $this->postRepository->update($id, $validated);
        return $this->success(['post' => $updated]);
    }

    /**
     * DELETE /api/v1/posts/{id}
     * Delete a post.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->postRepository->delete($id)) {
            return $this->error('Post not found', 404);
        }
        return $this->success(['message' => 'Post deleted'], 204);
    }

    /**
     * GET /api/v1/posts/author/{author}
     * Get all posts by an author.
     */
    public function getByAuthor(string $author): JsonResponse
    {
        $posts = $this->postRepository->getByAuthor($author);
        return $this->success([
            'posts' => $posts,
            'author' => $author,
            'total' => count($posts),
        ]);
    }

    private function success(array $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => 'v1',
            ],
        ], $status);
    }

    private function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'error' => $message,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => 'v1',
            ],
        ], $status);
    }
}
