<?php

namespace App\Providers;

use App\Api\Prolook\UserApi as ProlookUserApi;
use App\Api\Riddell\UserApi;
use App\Models\Cart;
use App\Models\CartItem;
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
                        // $user = User::findBy('access_token', $access_token)->first();

                        // if (!is_null($user))
                        // {
                        //     $app_config = config('app');
                        //     $jwt_config = config('jwt');

                        //     try {
                        //         JWT::decode($access_token, $app_config['key'], [$jwt_config['algorithm']]);

                        //         return $user;
                        //     } catch (ExpiredException $e) {
                        //         \Log::warning("Warning: Access Token is already expired.");
                        //     }
                        // }

                        $riddellApi = new UserApi($access_token);
                        $result = $riddellApi->getUserCart();

                        // check if valid token
                        if ($result->success)
                        {
                            $data = $result->data;

                            $user = User::findBy('email', $data->user_email)->first();

                            // create user if not exist
                            if (is_null($user))
                            {
                                $name = substr($data->user_email, 0, strpos($data->user_email, "@"));

                                User::create([
                                    'name' => $name,
                                    'email' => $data->user_email
                                ]);

                                $user = User::findBy('email', $data->user_email)->first();
                            }

                            // use quick register to update user_id
                            if (!$user->hasUserId())
                            {
                                $prolookApi = new ProlookUserApi;
                                $quickRegResult = $prolookApi->quickRegistration($user->email);

                                if ($quickRegResult->success)
                                {
                                    if (isset($quickRegResult->data))
                                    {
                                        $user->saveUserId($quickRegResult->data->user_id);
                                    }
                                }
                            }

                            $currentCart = $user->getCurrentCart();

                            // assign cart to user if user has no cart
                            if (!$user->hasValidCart())
                            {
                                $createdCart = Cart::create([
                                    'user_id' => $user->id,
                                    'pl_cart_id' => $data->pl_cart_id,
                                    'is_active' => Cart::TRUTHY_FLAG
                                ]);

                                $currentCart = Cart::find($createdCart->id);
                            }

                            // add cart item if not exist in cart
                            $items = $data->items;
                            foreach ($items as $item) {
                                $cartItem = CartItem::findBy('line_item_id', $item->line_item_id)->first();

                                if (is_null($cartItem))
                                {
                                    $currentCart->cart_items()->save(new CartItem([
                                        'cut_id' => $item->cut_id,
                                        'line_item_id' => $item->line_item_id
                                    ]));
                                    // create cart item
                                }
                                // elseif ($cartItem->cut_id !== $item->cut_id)
                                // {
                                //     // change cut id
                                // }
                            }

                            return $user;
                        }
                    }
                }
            }
        });
    }
}
