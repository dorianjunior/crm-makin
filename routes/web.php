<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Guest routes (unauthenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Authenticated routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

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

// Root route
Route::get('/', function () {
    return redirect()->route('dashboard');
});
