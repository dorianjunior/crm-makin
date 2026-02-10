<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CMS\PageController;
use App\Http\Controllers\CMS\SiteController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\Web\ActivityController;
use App\Http\Controllers\Web\LeadController;
use App\Http\Controllers\Web\PipelineController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\StageController;
use App\Http\Controllers\Web\TaskController;
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

    // Pipelines
    Route::resource('pipelines', PipelineController::class)->except(['show', 'create', 'edit']);
    Route::patch('pipelines/{pipeline}', [PipelineController::class, 'patch'])->name('pipelines.patch');
    Route::post('pipelines/{pipeline}/set-default', [PipelineController::class, 'setDefault'])->name('pipelines.setDefault');
    Route::post('pipelines/{pipeline}/stages/reorder', [PipelineController::class, 'reorderStages'])->name('pipelines.reorderStages');

    // Stages
    Route::resource('stages', StageController::class)->only(['store', 'update', 'destroy']);

    // Activities
    Route::resource('activities', ActivityController::class)->only(['index', 'store', 'update', 'destroy']);

    // Tasks
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])->name('tasks.toggleComplete');

    // Products - Inertia page wired to backend controller
    Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');

    // Proposals - Inertia page wired to backend controller
    Route::resource('proposals', ProposalController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('proposals/{proposal}/duplicate', [ProposalController::class, 'duplicate'])->name('proposals.duplicate');
    Route::post('proposals/{proposal}/send', [ProposalController::class, 'send'])->name('proposals.send');
    Route::get('proposals/{proposal}/download', [ProposalController::class, 'download'])->name('proposals.download');

    // CMS
    Route::resource('sites', SiteController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('sites/{site}/regenerate-api-key', [SiteController::class, 'regenerateApiKey'])->name('sites.regenerateApiKey');
    Route::patch('sites/{site}/toggle-active', [SiteController::class, 'toggleActive'])->name('sites.toggleActive');

    Route::resource('pages', PageController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('pages/{page}/publish', [PageController::class, 'publish'])->name('pages.publish');
    Route::post('pages/{page}/unpublish', [PageController::class, 'unpublish'])->name('pages.unpublish');
    Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');

    Route::get('/posts', function () {
        return Inertia::render('CMS/Posts/Index');
    })->name('posts.index');
    Route::get('/portfolios', function () {
        return Inertia::render('CMS/Portfolios/Index');
    })->name('portfolios.index');
    Route::get('/menus', function () {
        return Inertia::render('CMS/Menus/Index');
    })->name('menus.index');

    // Social
    Route::get('/instagram', function () {
        return Inertia::render('Social/Instagram/Messages');
    })->name('instagram.index');
    Route::get('/whatsapp', function () {
        return Inertia::render('Social/WhatsApp/Chat');
    })->name('whatsapp.index');
    Route::get('/message-templates', function () {
        return Inertia::render('Social/MessageTemplates/Index');
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
        return Inertia::render('CRM/Companies/Index');
    })->name('company.index');
    Route::get('/users', function () {
        return Inertia::render('Admin/Users/Index');
    })->name('users.index');
    Route::get('/roles', function () {
        return Inertia::render('Admin/Roles/Index');
    })->name('roles.index');
    Route::get('/permissions', function () {
        return Inertia::render('Admin/Index');
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
