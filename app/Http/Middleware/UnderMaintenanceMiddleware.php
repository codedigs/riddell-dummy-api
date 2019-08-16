<?php

namespace App\Http\Middleware;

use Closure;

class UnderMaintenanceMiddleware
{
    public function handle($request, Closure $next)
    {
        $is_under_maintenance = config("app.under_maintenance");

        if (!$is_under_maintenance)
        {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => "Application is under maintenance. Please try again later.",
        ]);
    }
}
