<?php

use App\Http\Controllers\Api\V1\WelcomeController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
| Versioned under /api/v1 so future breaking changes can live under /v2
| without affecting existing clients.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
    Route::get('/greet', [WelcomeController::class, 'greet'])->name('greet');
    Route::get('/health', [HealthController::class, 'check'])->name('health');

    // Post endpoints - demonstrates Postgres persistence
    Route::apiResource('posts', PostController::class);
    Route::get('posts/author/{author}', [PostController::class, 'getByAuthor'])->name('posts.author');
});
