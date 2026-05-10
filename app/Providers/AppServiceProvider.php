<?php

namespace App\Providers;

use App\Services\QurbanSettingsService;
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
        try {
            $this->app->make(QurbanSettingsService::class)->apply();
        } catch (\Throwable) {
            // Ignore during bootstrap when database is not ready yet.
        }
    }
}
