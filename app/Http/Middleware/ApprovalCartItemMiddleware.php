<?php

namespace App\Http\Middleware;

use App\Models\ClientInformation;
use Closure;

/**
 * use Approval middleware before use this
 */
class ApprovalCartItemMiddleware
{
    public function handle($request, Closure $next)
    {
        $authorization = $request->header("Authorization");
        list($type, $approval_token) = explode(" ", $authorization);

        $clientInfo = ClientInformation::findBy('approval_token', $approval_token)->first();
        $cartItem = $clientInfo->cart_item;

        if (!is_null($cartItem))
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
