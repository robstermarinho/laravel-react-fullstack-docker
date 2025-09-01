<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;
use App\Services\ServiceCnt;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register AuthService as singleton
        $this->app->singleton(AuthServiceInterface::class, function ($app) {
            return new AuthService($app->make(ServiceCnt::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
