<?php

use App\Http\Controllers\AIConversationController;
use App\Http\Controllers\AIPromptTemplateController;
use App\Http\Controllers\AISettingsController;
use App\Http\Controllers\API\CMS\BannerController;
use App\Http\Controllers\API\CMS\ContentApprovalController;
use App\Http\Controllers\API\CMS\FaqController;
use App\Http\Controllers\API\CMS\FormController;
use App\Http\Controllers\API\CMS\MenuController;
use App\Http\Controllers\API\CMS\PageController;
use App\Http\Controllers\API\CMS\PortfolioController;
use App\Http\Controllers\API\CMS\PostController;
use App\Http\Controllers\API\CMS\PreviewController;
use App\Http\Controllers\API\CMS\SiteController;
use App\Http\Controllers\API\CMS\TeamMemberController;
use App\Http\Controllers\API\CMS\TestimonialController;
use App\Http\Controllers\API\CMS\VersionController;
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
use App\Http\Controllers\API\Social\InstagramController;
use App\Http\Controllers\API\Social\InstagramWebhookController;
use App\Http\Controllers\API\Social\WhatsAppController;
use App\Http\Controllers\API\Social\WhatsAppWebhookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportScheduleController;
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
// PUBLIC ROUTES - CMS Preview (No Auth)
// ============================================
Route::prefix('cms/preview')->group(function () {
    Route::get('/{type}/{id}/{token}', [PreviewController::class, 'show']);
});

// ============================================
// PUBLIC ROUTES - Instagram Webhook (No Auth)
// ============================================
Route::prefix('webhooks/instagram')->group(function () {
    Route::get('/verify', [InstagramWebhookController::class, 'verify']); // Meta webhook verification
    Route::post('/handle', [InstagramWebhookController::class, 'handle']); // Incoming messages
});

// ============================================
// PUBLIC ROUTES - WhatsApp Webhook (No Auth)
// ============================================
Route::prefix('webhooks/whatsapp')->group(function () {
    Route::get('/verify', [WhatsAppWebhookController::class, 'verify']); // Meta webhook verification
    Route::post('/handle', [WhatsAppWebhookController::class, 'handle']); // Incoming messages & status
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
        // ============================================
        // SOCIAL MEDIA INTEGRATION ROUTES
        // ============================================
        Route::prefix('social')->group(function () {
            // Instagram
            Route::prefix('instagram')->group(function () {
                Route::get('/auth-url', [InstagramController::class, 'getAuthUrl']);
                Route::post('/connect', [InstagramController::class, 'connect']);
                Route::get('/accounts', [InstagramController::class, 'index']);
                Route::get('/accounts/{account}/messages', [InstagramController::class, 'getMessages']);
                Route::get('/accounts/{account}/posts', [InstagramController::class, 'getPosts']);
                Route::post('/accounts/{account}/refresh-token', [InstagramController::class, 'refreshToken']);
                Route::delete('/accounts/{account}/disconnect', [InstagramController::class, 'disconnect']);
            });

            // WhatsApp
            Route::prefix('whatsapp')->group(function () {
                Route::get('/accounts', [WhatsAppController::class, 'index']);
                Route::post('/accounts', [WhatsAppController::class, 'store']);
                Route::get('/accounts/{account}/conversations', [WhatsAppController::class, 'conversations']);
                Route::get('/conversations/{conversation}/messages', [WhatsAppController::class, 'messages']);
                Route::post('/accounts/{account}/send', [WhatsAppController::class, 'sendMessage']);
                Route::post('/accounts/{account}/send-media', [WhatsAppController::class, 'sendMedia']);
                Route::post('/conversations/{conversation}/mark-read', [WhatsAppController::class, 'markAsRead']);
                Route::delete('/accounts/{account}/disconnect', [WhatsAppController::class, 'disconnect']);
            });
        });

        // ============================================
        // AI INTEGRATION ROUTES
        // ============================================
        Route::prefix('ai')->group(function () {
            // AI Settings
            Route::apiResource('settings', AISettingsController::class);
            Route::post('settings/{id}/set-default', [AISettingsController::class, 'setDefault']);
            Route::post('settings/{id}/test', [AISettingsController::class, 'test']);

            // Prompt Templates
            Route::apiResource('templates', AIPromptTemplateController::class);
            Route::post('templates/{id}/preview', [AIPromptTemplateController::class, 'preview']);
            Route::get('templates/{id}/statistics', [AIPromptTemplateController::class, 'statistics']);

            // Conversations
            Route::apiResource('conversations', AIConversationController::class);
            Route::post('conversations/{id}/send-message', [AIConversationController::class, 'sendMessage']);
            Route::post('conversations/{id}/complete', [AIConversationController::class, 'complete']);
            Route::get('conversations/statistics', [AIConversationController::class, 'statistics']);
        });

        // ============================================
        // NOTIFICATION ROUTES
        // ============================================
        Route::prefix('notifications')->group(function () {
            // Notifications
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/statistics', [NotificationController::class, 'statistics']);
            Route::get('/{id}', [NotificationController::class, 'show']);
            Route::post('/', [NotificationController::class, 'store']);
            Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
            Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
            Route::post('/test', [NotificationController::class, 'sendTest']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);

            // Preferences
            Route::prefix('preferences')->group(function () {
                Route::get('/', [NotificationPreferenceController::class, 'index']);
                Route::get('/type/{type}', [NotificationPreferenceController::class, 'getByType']);
                Route::get('/{id}', [NotificationPreferenceController::class, 'show']);
                Route::post('/', [NotificationPreferenceController::class, 'store']);
                Route::put('/{id}', [NotificationPreferenceController::class, 'update']);
                Route::post('/{id}/enable/{channel}', [NotificationPreferenceController::class, 'enableChannel']);
                Route::post('/{id}/disable/{channel}', [NotificationPreferenceController::class, 'disableChannel']);
                Route::post('/reset-default', [NotificationPreferenceController::class, 'resetToDefault']);
                Route::delete('/{id}', [NotificationPreferenceController::class, 'destroy']);
            });

            // Templates
            Route::prefix('templates')->group(function () {
                Route::get('/', [NotificationTemplateController::class, 'index']);
                Route::get('/{id}', [NotificationTemplateController::class, 'show']);
                Route::post('/', [NotificationTemplateController::class, 'store']);
                Route::put('/{id}', [NotificationTemplateController::class, 'update']);
                Route::post('/{id}/preview', [NotificationTemplateController::class, 'preview']);
                Route::get('/{id}/variables', [NotificationTemplateController::class, 'variables']);
                Route::delete('/{id}', [NotificationTemplateController::class, 'destroy']);
            });
        });

        // ============================================
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

        // Content Approvals
        Route::prefix('approvals')->group(function () {
            Route::get('/', [ContentApprovalController::class, 'index']);
            Route::get('/statistics', [ContentApprovalController::class, 'statistics']);
            Route::get('/{approval}', [ContentApprovalController::class, 'show']);
            Route::post('/{approval}/approve', [ContentApprovalController::class, 'approve']);
            Route::post('/{approval}/reject', [ContentApprovalController::class, 'reject']);
        });

        // Preview & Versioning
        Route::prefix('preview')->group(function () {
            Route::post('/{type}/{id}/token', [PreviewController::class, 'generateToken']);
            Route::delete('/tokens/{token}', [PreviewController::class, 'revokeToken']);
        });

        Route::prefix('versions')->group(function () {
            Route::get('/{type}/{id}', [VersionController::class, 'index']);
            Route::get('/{type}/{id}/{versionNumber}', [VersionController::class, 'show']);
            Route::post('/{type}/{id}', [VersionController::class, 'store']);
            Route::post('/{type}/{id}/rollback/{versionNumber}', [VersionController::class, 'rollback']);
            Route::post('/{type}/{id}/compare', [VersionController::class, 'compare']);
        });

        // Portfolios
        Route::apiResource('portfolios', PortfolioController::class);
        Route::post('portfolios/{portfolio}/publish', [PortfolioController::class, 'publish']);
        Route::post('portfolios/{portfolio}/unpublish', [PortfolioController::class, 'unpublish']);
        Route::post('portfolios/{portfolio}/request-approval', [PortfolioController::class, 'requestApproval']);

        // FAQs
        Route::apiResource('faqs', FaqController::class);
        Route::post('faqs/{faq}/publish', [FaqController::class, 'publish']);
        Route::post('faqs/{faq}/unpublish', [FaqController::class, 'unpublish']);
        Route::post('faqs/{faq}/request-approval', [FaqController::class, 'requestApproval']);

        // Testimonials
        Route::apiResource('testimonials', TestimonialController::class);
        Route::post('testimonials/{testimonial}/publish', [TestimonialController::class, 'publish']);
        Route::post('testimonials/{testimonial}/unpublish', [TestimonialController::class, 'unpublish']);
        Route::post('testimonials/{testimonial}/request-approval', [TestimonialController::class, 'requestApproval']);

        // Team Members
        Route::apiResource('team-members', TeamMemberController::class);
        Route::post('team-members/{teamMember}/publish', [TeamMemberController::class, 'publish']);
        Route::post('team-members/{teamMember}/unpublish', [TeamMemberController::class, 'unpublish']);
        Route::post('team-members/{teamMember}/request-approval', [TeamMemberController::class, 'requestApproval']);

        // Forms
        Route::apiResource('forms', FormController::class);
        Route::post('forms/{form}/activate', [FormController::class, 'activate']);
        Route::post('forms/{form}/deactivate', [FormController::class, 'deactivate']);

        // Banners
        Route::apiResource('banners', BannerController::class);
        Route::post('banners/{banner}/publish', [BannerController::class, 'publish']);
        Route::post('banners/{banner}/unpublish', [BannerController::class, 'unpublish']);
        Route::post('banners/{banner}/request-approval', [BannerController::class, 'requestApproval']);
    });

    // ========================================
    // REPORTS & DASHBOARDS
    // ========================================

    // Dashboards
    Route::prefix('dashboards')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/leads', [DashboardController::class, 'leads']);
        Route::get('/sales', [DashboardController::class, 'sales']);
        Route::get('/activities', [DashboardController::class, 'activities']);
        Route::get('/tasks', [DashboardController::class, 'tasks']);
        Route::get('/conversion', [DashboardController::class, 'conversion']);
        Route::get('/top-performers', [DashboardController::class, 'topPerformers']);
        Route::get('/sales-funnel', [DashboardController::class, 'salesFunnel']);
        Route::get('/realtime', [DashboardController::class, 'realTime']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/types', [ReportController::class, 'types']);
        Route::get('/columns/{type}', [ReportController::class, 'columns']);
        Route::get('/filters/{type}', [ReportController::class, 'filters']);
        Route::get('/{id}', [ReportController::class, 'show']);
        Route::post('/', [ReportController::class, 'store']);
        Route::put('/{id}', [ReportController::class, 'update']);
        Route::delete('/{id}', [ReportController::class, 'destroy']);
        Route::post('/{id}/execute', [ReportController::class, 'execute']);
        Route::post('/{id}/export', [ReportController::class, 'export']);
        Route::get('/{id}/exports/{exportId}/download', [ReportController::class, 'downloadExport']);
        Route::post('/{id}/duplicate', [ReportController::class, 'duplicate']);
        Route::post('/{id}/toggle-favorite', [ReportController::class, 'toggleFavorite']);

        // Report Schedules
        Route::prefix('{reportId}/schedules')->group(function () {
            Route::get('/', [ReportScheduleController::class, 'index']);
            Route::get('/frequencies', [ReportScheduleController::class, 'frequencies']);
            Route::get('/formats', [ReportScheduleController::class, 'formats']);
            Route::get('/{id}', [ReportScheduleController::class, 'show']);
            Route::post('/', [ReportScheduleController::class, 'store']);
            Route::put('/{id}', [ReportScheduleController::class, 'update']);
            Route::delete('/{id}', [ReportScheduleController::class, 'destroy']);
            Route::post('/{id}/activate', [ReportScheduleController::class, 'activate']);
            Route::post('/{id}/deactivate', [ReportScheduleController::class, 'deactivate']);
            Route::post('/{id}/recipients', [ReportScheduleController::class, 'addRecipient']);
            Route::delete('/{id}/recipients', [ReportScheduleController::class, 'removeRecipient']);
        });
    });
});
