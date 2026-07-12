<?php

namespace App\Repositories;

use App\Contracts\PostRepository;

class InMemoryPostRepository implements PostRepository
{
    private array $posts = [];
    private int $nextId = 1;

    public function __construct()
    {
        // Initialize with sample data
        $this->posts = [
            1 => [
                'id' => 1,
                'title' => 'Welcome to In-Memory Posts',
                'content' => 'This data is NOT persisted across restarts.',
                'author' => 'System',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ];
        $this->nextId = 2;
    }

    public function all(): array
    {
        return array_values($this->posts);
    }

    public function getById(int $id): ?array
    {
        return $this->posts[$id] ?? null;
    }

    public function create(array $data): array
    {
        $id = $this->nextId++;
        $post = array_merge($data, [
            'id' => $id,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);
        $this->posts[$id] = $post;
        return $post;
    }

    public function update(int $id, array $data): ?array
    {
        if (!isset($this->posts[$id])) {
            return null;
        }
        $this->posts[$id] = array_merge($this->posts[$id], $data, [
            'updated_at' => now()->toIso8601String(),
        ]);
        return $this->posts[$id];
    }

    public function delete(int $id): bool
    {
        if (isset($this->posts[$id])) {
            unset($this->posts[$id]);
            return true;
        }
        return false;
    }

    public function getByAuthor(string $author): array
    {
        return array_values(
            array_filter($this->posts, fn($post) => $post['author'] === $author)
        );
    }

    public function count(): int
    {
        return count($this->posts);
    }
}
