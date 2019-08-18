<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;

/**
 * use Cart middleware before use this
 */
class CartItemMiddleware
{
    public function handle($request, Closure $next)
    {
        $cart_token = $request->get('cart_token');
        $cart = Cart::findByToken($cart_token);

        $cart_item_ids = $cart->cart_items->pluck("id")->toArray();

        $cart_item_id = $request->route()[2]['cart_item_id'];

        if (in_array($cart_item_id, $cart_item_ids))
        {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart"
        ]);
    }
}
