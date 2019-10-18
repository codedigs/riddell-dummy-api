<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;

/**
 * use Cart middleware before use this
 */
class ValidToSubmitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        if ($currentCart->areAllItemsApproved())
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
