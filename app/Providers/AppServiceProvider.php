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
        $this->app->configure("app");
        $this->app->configure("jwt");
        $this->app->configure("prolook");
        $this->app->configure("qx7");
        $this->app->configure("riddell");
        $this->app->configure("pdf");

        $this->app->singleton('mailer', function ($app) {
            $app->configure('services');
            return $app->loadComponent('mail', "Illuminate\Mail\MailServiceProvider", 'mailer');
        });
    }
}
