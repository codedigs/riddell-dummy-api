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
        $user = $request->user();
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        if (!is_null($currentCart))
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
