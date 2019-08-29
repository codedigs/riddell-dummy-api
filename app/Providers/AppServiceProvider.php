<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app()->configure("app");
        app()->configure("jwt");
        app()->configure("prolook");
        app()->configure("qx7");
        app()->configure("riddell");
    }
}
