<?php

namespace App\Providers;

use App\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $authorization = $request->header("Authorization");

            if (!is_null($authorization))
            {
                list($type, $access_token) = explode(" ", $authorization);

                if (strtolower($type) === "bearer")
                {
                    if (!is_null($access_token))
                    {
                        $user = User::findBy('access_token', $access_token)->first();

                        if (!is_null($user))
                        {
                            $app_config = config('app');
                            $jwt_config = config('jwt');

                            try {
                                JWT::decode($access_token, $app_config['key'], [$jwt_config['algorithm']]);

                                return $user;
                            } catch (ExpiredException $e) {
                                \Log::warning("Warning: Access Token is already expired.");
                            }
                        }
                    }
                }
            }
        });
    }
}
