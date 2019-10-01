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

                            if (!$user->hasUserId())
                            {
                                $prolookApi = new ProlookUserApi;

                                // use quick register to update user_id
                                $quickRegResult = $prolookApi->quickRegistration($user->email);

                                if ($quickRegResult->success)
                                {
                                    if (isset($quickRegResult->data))
                                    {
                                        $user_id = $quickRegResult->data->user_id;
                                        $prolook_access_token = $quickRegResult->accessToken;

                                        $user->saveUserIdAndAccessToken($user_id, $prolook_access_token);
                                    }

                                    goto endQuickRegistrationProcess;
                                }

                                // else if email existing in prolook api
                                $emailAvailableResult = $prolookApi->isEmailAvailable($user->email);

                                if ($emailAvailableResult->success)
                                {
                                    if (isset($emailAvailableResult->user))
                                    {
                                        if (isset($emailAvailableResult->user->user_id))
                                        {
                                            $prolook_access_token = "";

                                            if (isset($emailAvailableResult->user->access_token))
                                            {
                                                $prolook_access_token = $emailAvailableResult->user->access_token->access_token;
                                            }

                                            $user->saveUserIdAndAccessToken($emailAvailableResult->user->user_id, $prolook_access_token);
                                        }
                                    }
                                }
                            }

                            # end quick registration process
                            endQuickRegistrationProcess:

                            $currentCart = Cart::findBy('pl_cart_id', $data->pl_cart_id)->first();

                            // create cart if not exist
                            if (is_null($currentCart))
                            {
                                $user->carts()->save(new Cart([
                                    'pl_cart_id' => $data->pl_cart_id,
                                    'is_active' => Cart::TRUTHY_FLAG
                                ]));

                                $currentCart = Cart::findBy('pl_cart_id', $data->pl_cart_id)->first();
                            }

                            // add cart item if not exist in cart
                            $items = $data->items;
                            foreach ($items as $item) {
                                $cartItem = CartItem::withTrashed()
                                                    ->findBy('line_item_id', $item->line_item_id)
                                                    ->first();

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

                            // add extra data
                            $user->hybris_access_token = $access_token;
                            $user->current_pl_cart_id = $data->pl_cart_id;

                            return $user;
                        }
                    }
                }
            }
        });
    }
}
