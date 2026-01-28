<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\API\CMS\MenuController;
use App\Http\Controllers\API\CMS\PageController;
use App\Http\Controllers\API\CMS\PostController;
use App\Http\Controllers\API\CMS\SiteController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadSourceController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappMessageController;
use Illuminate\Support\Facades\Route;

// ============================================
// PUBLIC ROUTES - Authentication
// ============================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================
// PROTECTED ROUTES - Require Authentication
// ============================================
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });

    // Companies
    Route::apiResource('companies', CompanyController::class);

    // Roles & Permissions
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    // Users
    Route::apiResource('users', UserController::class);

    // Lead Management
    Route::apiResource('lead-sources', LeadSourceController::class);
    Route::apiResource('leads', LeadController::class);
    Route::apiResource('activities', ActivityController::class);
    Route::apiResource('tasks', TaskController::class);

    // Pipeline Management
    Route::apiResource('pipelines', PipelineController::class);
    Route::apiResource('pipeline-stages', PipelineStageController::class);
    Route::post('pipeline-stages/{pipelineStage}/leads', [PipelineStageController::class, 'attachLead']);
    Route::delete('pipeline-stages/{pipelineStage}/leads/{leadId}', [PipelineStageController::class, 'detachLead']);

    // Products & Proposals
    Route::apiResource('products', ProductController::class);
    Route::apiResource('proposals', ProposalController::class);

    // Communication
    Route::apiResource('emails', EmailController::class);
    Route::apiResource('whatsapp-messages', WhatsappMessageController::class);
    Route::apiResource('message-templates', MessageTemplateController::class);

    // Files
    Route::apiResource('files', FileController::class)->except(['update']);
    Route::get('files/{file}/download', [FileController::class, 'download']);

    // System Logs
    Route::apiResource('system-logs', SystemLogController::class)->only(['index', 'show']);

    // ============================================
    // CMS ROUTES
    // ============================================
    Route::prefix('cms')->group(function () {
        // Sites
        Route::apiResource('sites', SiteController::class);
        Route::post('sites/{site}/regenerate-key', [SiteController::class, 'regenerateApiKey']);
        Route::post('sites/{site}/activate', [SiteController::class, 'activate']);
        Route::post('sites/{site}/deactivate', [SiteController::class, 'deactivate']);

        // Pages
        Route::apiResource('pages', PageController::class);
        Route::post('pages/{page}/publish', [PageController::class, 'publish']);
        Route::post('pages/{page}/unpublish', [PageController::class, 'unpublish']);
        Route::post('pages/{page}/request-approval', [PageController::class, 'requestApproval']);

        // Posts
        Route::apiResource('posts', PostController::class);
        Route::post('posts/{post}/publish', [PostController::class, 'publish']);
        Route::post('posts/{post}/unpublish', [PostController::class, 'unpublish']);
        Route::post('posts/{post}/request-approval', [PostController::class, 'requestApproval']);

        // Menus
        Route::apiResource('menus', MenuController::class);
    });
});
