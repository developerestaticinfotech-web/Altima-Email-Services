<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind EmailService to the container
        $this->app->singleton(\App\Services\EmailService::class, function ($app) {
            return new \App\Services\EmailService();
        });
        
        // Bind RabbitMQService to the container
        $this->app->singleton(\App\Services\RabbitMQService::class, function ($app) {
            return new \App\Services\RabbitMQService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
