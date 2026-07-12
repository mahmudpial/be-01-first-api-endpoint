<?php

namespace App\Providers;

use App\Contracts\PostRepository;
use App\Repositories\PostgresPostRepository;
use Illuminate\Support\ServiceProvider;

class PostRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the PostRepository interface to PostgresPostRepository
        // To switch to in-memory, change to:
        // $this->app->singleton(PostRepository::class, InMemoryPostRepository::class);
        $this->app->singleton(PostRepository::class, PostgresPostRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
