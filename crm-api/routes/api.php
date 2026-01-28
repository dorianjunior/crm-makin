<?php

use App\Http\Controllers\API\CMS\MenuController;
use App\Http\Controllers\API\CMS\PageController;
use App\Http\Controllers\API\CMS\PostController;
use App\Http\Controllers\API\CMS\SiteController;
use App\Http\Controllers\API\CRM\ActivityController;
use App\Http\Controllers\API\CRM\CompanyController;
use App\Http\Controllers\API\CRM\EmailController;
use App\Http\Controllers\API\CRM\FileController;
use App\Http\Controllers\API\CRM\LeadController;
use App\Http\Controllers\API\CRM\LeadSourceController;
use App\Http\Controllers\API\CRM\MessageTemplateController;
use App\Http\Controllers\API\CRM\PipelineController;
use App\Http\Controllers\API\CRM\PipelineStageController;
use App\Http\Controllers\API\CRM\ProductController;
use App\Http\Controllers\API\CRM\ProposalController;
use App\Http\Controllers\API\CRM\SystemLogController;
use App\Http\Controllers\API\CRM\TaskController;
use App\Http\Controllers\API\CRM\WhatsappMessageController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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

    // Roles & Permissions
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    // Users
    Route::apiResource('users', UserController::class);

    // ============================================
    // CRM ROUTES
    // ============================================
    Route::prefix('crm')->group(function () {
        // Companies
        Route::apiResource('companies', CompanyController::class);
        Route::post('companies/{company}/activate', [CompanyController::class, 'activate']);
        Route::post('companies/{company}/deactivate', [CompanyController::class, 'deactivate']);
        Route::post('companies/{company}/suspend', [CompanyController::class, 'suspend']);

        // Lead Management
        Route::apiResource('lead-sources', LeadSourceController::class);
        Route::apiResource('leads', LeadController::class);
        Route::post('leads/{lead}/assign', [LeadController::class, 'assign']);
        Route::post('leads/{lead}/change-status', [LeadController::class, 'changeStatus']);

        // Activities
        Route::apiResource('activities', ActivityController::class);
        Route::get('activities/recent', [ActivityController::class, 'recent']);

        // Tasks
        Route::apiResource('tasks', TaskController::class);
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);
        Route::post('tasks/{task}/cancel', [TaskController::class, 'cancel']);
        Route::get('tasks/overdue', [TaskController::class, 'overdue']);
        Route::get('tasks/upcoming', [TaskController::class, 'upcoming']);

        // Pipeline Management
        Route::apiResource('pipelines', PipelineController::class);
        Route::post('pipelines/{pipeline}/stages', [PipelineController::class, 'addStage']);
        Route::put('pipelines/{pipeline}/stages/{stageId}', [PipelineController::class, 'updateStage']);
        Route::post('pipelines/{pipeline}/reorder-stages', [PipelineController::class, 'reorderStages']);

        Route::apiResource('pipeline-stages', PipelineStageController::class);

        // Products & Proposals
        Route::apiResource('products', ProductController::class);
        Route::post('products/{product}/activate', [ProductController::class, 'activate']);
        Route::post('products/{product}/deactivate', [ProductController::class, 'deactivate']);

        Route::apiResource('proposals', ProposalController::class);
        Route::post('proposals/{proposal}/accept', [ProposalController::class, 'accept']);
        Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject']);
        Route::post('proposals/{proposal}/items', [ProposalController::class, 'addItem']);
        Route::delete('proposals/{proposal}/items/{itemId}', [ProposalController::class, 'removeItem']);

        // Communication
        Route::apiResource('emails', EmailController::class);
        Route::apiResource('whatsapp-messages', WhatsappMessageController::class);
        Route::apiResource('message-templates', MessageTemplateController::class);

        // Files
        Route::apiResource('files', FileController::class)->except(['update']);
        Route::get('files/{file}/download', [FileController::class, 'download']);

        // System Logs
        Route::apiResource('system-logs', SystemLogController::class)->only(['index', 'show']);
    });

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
