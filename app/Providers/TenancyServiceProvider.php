<?php
// app/Providers/TenancyServiceProvider.php

namespace App\Providers;

use App\Support\Tenancy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // @single ... @endsingle  /  @multi ... @endmulti
        Blade::if('single', fn () => Tenancy::isSingle());
        Blade::if('multi',  fn () => Tenancy::isMulti());

        View::share('tenancyMode', Tenancy::mode());
        View::share('tenancyOrg', Tenancy::organization());
    }
}