<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;

/**
 * use Authenticate middleware before use this
 */
class CartMiddleware
{
    public function handle($request, Closure $next)
    {
        $cart_token = $request->get('cart_token');

        // is cart token defined
        if (!is_null($cart_token))
        {
            $cart = Cart::findByToken($cart_token);

            // is cart token valid
            if (!is_null($cart))
            {
                $user = $request->user();

                // is user the owner of cart
                if ($cart->user->id === $user->id)
                {
                    return $next($request);
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart"
        ]);
    }
}
