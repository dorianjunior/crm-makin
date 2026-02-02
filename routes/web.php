<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function () {
        $stats = [
            'leads' => \App\Models\Lead::count(),
            'pages' => \App\Models\CMS\Page::count(),
            'posts' => \App\Models\CMS\Post::count(),
            'messages' => 0,
        ];

        return Inertia::render('Dashboard', ['stats' => $stats]);
    })->name('dashboard');
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});
