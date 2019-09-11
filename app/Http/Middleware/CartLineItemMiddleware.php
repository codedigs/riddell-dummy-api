<?php

namespace App\Http\Middleware;

use Closure;

/**
 * use Cart middleware before use this
 */
class CartLineItemMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $currentCart = $user->getCurrentCart();

        $line_item_ids = $currentCart->cart_items->pluck("line_item_id")->toArray();

        $line_item_id = $request->route()[2]['line_item_id'];

        if (in_array($line_item_id, $line_item_ids))
        {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart",
            'status_code' => 401
        ]);
    }
}