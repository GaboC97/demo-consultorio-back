<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ConversationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ConversationService::class, fn() => new ConversationService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
