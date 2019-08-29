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
        $user = $request->user();
        $currentCart = $user->getCurrentCart();

        $cart_item_ids = $currentCart->cart_items->pluck("id")->toArray();

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
