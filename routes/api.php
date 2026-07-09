<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello! This is my first API endpoint',
    ]);
});

Route::get('/greet', function () {
    return response()->json([
        'name' => 'Pial Mahmud',
        'role' => 'Backend AI Engineer',
        'stack' => ['Laravel', 'Vue.js', 'MySQL', 'JWT'],
    ]);
});

