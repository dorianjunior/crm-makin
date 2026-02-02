<?php

namespace App\Providers;

use App\Listeners\CMS\LogContentActivity;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        // Register CMS event subscriber
        Event::subscribe(LogContentActivity::class);
    }
}
