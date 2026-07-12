<?php

namespace App\Repositories;

use App\Contracts\PostRepository;
use App\Models\Post;

class PostgresPostRepository implements PostRepository
{
    public function all(): array
    {
        return Post::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($post) => $this->formatPost($post))
            ->toArray();
    }

    public function getById(int $id): ?array
    {
        $post = Post::find($id);
        return $post ? $this->formatPost($post) : null;
    }

    public function create(array $data): array
    {
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'author' => $data['author'] ?? 'Anonymous',
        ]);
        return $this->formatPost($post);
    }

    public function update(int $id, array $data): ?array
    {
        $post = Post::find($id);
        if (!$post) {
            return null;
        }
        $post->update($data);
        return $this->formatPost($post);
    }

    public function delete(int $id): bool
    {
        return (bool) Post::destroy($id);
    }

    public function getByAuthor(string $author): array
    {
        return Post::where('author', $author)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($post) => $this->formatPost($post))
            ->toArray();
    }

    public function count(): int
    {
        return Post::count();
    }

    private function formatPost(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'author' => $post->author,
            'created_at' => $post->created_at->toIso8601String(),
            'updated_at' => $post->updated_at->toIso8601String(),
        ];
    }
}
