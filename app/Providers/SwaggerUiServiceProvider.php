<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SwaggerUiServiceProvider extends ServiceProvider
{
    public function boot() : void
    {
        Gate::define('viewSwaggerUI', function ($user = null) {
            return in_array(optional($user)->email, [
                'path' => 'swagger',
                'versions' => [
                    'v1' => resource_path('swagger/openapi.json')
                ],
                'server_url' => 'http://127.0.0.1:8000/api',
                'server_variables' => ['Language' => ['default' => 'en']],
            ]);
        });
    }
}
