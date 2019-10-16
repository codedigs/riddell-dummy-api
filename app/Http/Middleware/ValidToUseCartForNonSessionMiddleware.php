<?php

namespace App\Http\Middleware;

use App\Models\ClientInformation;
use Closure;

/**
 * use ApprovalCartItem middleware before use this
 */
class ValidToUseCartForNonSessionMiddleware
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
        $authorization = $request->header("Authorization");
        list($type, $approval_token) = explode(" ", $authorization);

        $clientInfo = ClientInformation::findBy('approval_token', $approval_token)->first();
        $currentCart = $clientInfo->cart_item->cart;

        if (!is_null($currentCart))
        {
            // check if currentCart not already completed
            if (!$currentCart->isCompleted())
            {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart",
            'status_code' => 401
        ]);
    }
}
