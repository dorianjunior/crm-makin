<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rota temporária sem autenticação para testar
Route::get('/test-dashboard', function () {
    $stats = [
        'leads' => 0,
        'pages' => 0,
        'posts' => 0,
        'messages' => 0,
    ];

    return Inertia::render('Dashboard', ['stats' => $stats]);
})->name('test.dashboard');

// Rota de login temporária
Route::get('/login', function () {
    return response()->json([
        'message' => 'Login page - TODO: implement authentication',
        'tip' => 'Use /test-dashboard for now',
    ]);
})->name('login');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function () {
        $stats = [
            'leads' => 0,
            'pages' => 0,
            'posts' => 0,
            'messages' => 0,
        ];

        return Inertia::render('Dashboard', ['stats' => $stats]);
    })->name('dashboard');
});

Route::get('/', function () {
    return redirect()->route('test.dashboard');
});
