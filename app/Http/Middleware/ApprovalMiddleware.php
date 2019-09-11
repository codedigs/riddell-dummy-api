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
        $authorization = $request->header("Authorization");

        if (!is_null($authorization))
        {
            list($type, $approval_token) = explode(" ", $authorization);

            if (strtolower($type) === "bearer")
            {
                $clientInfo = ClientInformation::findBy('approval_token', $approval_token)->first();

                if (!is_null($clientInfo))
                {
                    return $next($request);
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart",
            'status_code' => 401
        ]);
    }
}
