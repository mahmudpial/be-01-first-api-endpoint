<?php

namespace App\Contracts;

interface PostRepository
{
    /**
     * Get all posts.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Get a single post by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array;

    /**
     * Create a new post.
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array;

    /**
     * Update an existing post.
     *
     * @param int $id
     * @param array $data
     * @return array|null
     */
    public function update(int $id, array $data): ?array;

    /**
     * Delete a post.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get posts by author.
     *
     * @param string $author
     * @return array
     */
    public function getByAuthor(string $author): array;

    /**
     * Count total posts.
     *
     * @return int
     */
    public function count(): int;
}
