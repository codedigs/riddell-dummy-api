<?php

namespace App\Http\Middleware;

use App\Models\ClientInformation;
use Closure;

/**
 * use Authenticate middleware before use this
 */
class ApprovalMiddleware
{
    public function handle($request, Closure $next)
    {
        $approval_token = $request->route()[2]['approval_token'];

        $clientInfo = ClientInformation::findBy('approval_token', $approval_token)->first();

        if (!is_null($clientInfo))
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
