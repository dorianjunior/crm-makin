<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\LeadController;
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

    // CRM
    Route::resource('leads', LeadController::class);
    Route::get('/pipelines', function () {
        return Inertia::render('Pipelines/Index');
    })->name('pipelines.index');
    Route::get('/activities', function () {
        return Inertia::render('Activities/Index');
    })->name('activities.index');
    Route::get('/tasks', function () {
        return Inertia::render('Tasks/Index');
    })->name('tasks.index');
    Route::get('/products', function () {
        return Inertia::render('Products/Index');
    })->name('products.index');
    Route::get('/proposals', function () {
        return Inertia::render('Proposals/Index');
    })->name('proposals.index');

    // CMS
    Route::get('/sites', function () {
        return Inertia::render('Sites/Index');
    })->name('sites.index');
    Route::get('/pages', function () {
        return Inertia::render('Pages/Index');
    })->name('pages.index');
    Route::get('/posts', function () {
        return Inertia::render('Posts/Index');
    })->name('posts.index');
    Route::get('/portfolios', function () {
        return Inertia::render('Portfolios/Index');
    })->name('portfolios.index');
    Route::get('/menus', function () {
        return Inertia::render('Menus/Index');
    })->name('menus.index');

    // Social
    Route::get('/instagram', function () {
        return Inertia::render('Instagram/Index');
    })->name('instagram.index');
    Route::get('/whatsapp', function () {
        return Inertia::render('Whatsapp/Index');
    })->name('whatsapp.index');
    Route::get('/message-templates', function () {
        return Inertia::render('MessageTemplates/Index');
    })->name('message-templates.index');

    // IA & Automação
    Route::get('/ai/conversations', function () {
        return Inertia::render('Ai/Conversations/Index');
    })->name('ai.conversations.index');
    Route::get('/ai/prompts', function () {
        return Inertia::render('Ai/Prompts/Index');
    })->name('ai.prompts.index');
    Route::get('/ai/settings', function () {
        return Inertia::render('Ai/Settings/Index');
    })->name('ai.settings.index');

    // Notificações
    Route::get('/notifications', function () {
        return Inertia::render('Notifications/Index');
    })->name('notifications.index');
    Route::get('/notifications/preferences', function () {
        return Inertia::render('Notifications/Preferences');
    })->name('notifications.preferences');

    // Relatórios
    Route::get('/reports', function () {
        return Inertia::render('Reports/Index');
    })->name('reports.index');
    Route::get('/dashboards', function () {
        return Inertia::render('Dashboards/Index');
    })->name('dashboards.index');

    // Configurações
    Route::get('/company', function () {
        return Inertia::render('Companies/Index');
    })->name('company.index');
    Route::get('/users', function () {
        return Inertia::render('Admin/Users/Index');
    })->name('users.index');
    Route::get('/roles', function () {
        return Inertia::render('Admin/Roles/Index');
    })->name('roles.index');
    Route::get('/permissions', function () {
        return Inertia::render('Permissions/Index');
    })->name('permissions.index');

    // Outros
    Route::get('/profile', function () {
        return Inertia::render('Profile/Index');
    })->name('profile.index');
    Route::get('/settings', function () {
        return Inertia::render('Settings/Index');
    })->name('settings.index');
    Route::get('/help', function () {
        return Inertia::render('Help/Index');
    })->name('help.index');
});

// Root route
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Fallback for 404
Route::fallback(function () {
    return Inertia::render('Error/NotFound');
});
