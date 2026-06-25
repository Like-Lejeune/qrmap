<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QrCodeService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    public function register(): void
    {
       // $app->bind(\App\Services\QrCodeService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
