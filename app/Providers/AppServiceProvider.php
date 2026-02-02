<?php

namespace App\Providers;

use App\Models\CMS\ContentApproval;
use App\Models\CMS\Page;
use App\Models\CMS\Post;
use App\Models\CMS\Site;
use App\Policies\CMS\ContentApprovalPolicy;
use App\Policies\CMS\PagePolicy;
use App\Policies\CMS\PostPolicy;
use App\Policies\CMS\SitePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Site::class => SitePolicy::class,
        Page::class => PagePolicy::class,
        Post::class => PostPolicy::class,
        ContentApproval::class => ContentApprovalPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Register CMS event subscribers
        Event::subscribe(\App\Listeners\CMS\LogContentActivity::class);
        Event::subscribe(\App\Listeners\CMS\LogContentChanges::class);

        // Register CMS event listeners
        Event::listen(
            \App\Events\CMS\ApprovalRequested::class,
            \App\Listeners\CMS\NotifyManagersOfApprovalRequest::class
        );

        Event::listen(
            \App\Events\CMS\ContentPublished::class,
            \App\Listeners\CMS\NotifyContentPublished::class
        );

        Event::listen(
            \App\Events\CMS\ApprovalProcessed::class,
            \App\Listeners\CMS\NotifyApprovalProcessed::class
        );
    }
}
