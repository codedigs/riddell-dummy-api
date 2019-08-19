<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;

class CoachRequestLogController extends Controller
{
    /**
     * Get logs of cart item
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - cart_token
     *
     * @param Request $request
     */
    public function getAll(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        $logs = $cartItem->coach_request_logs->toArray();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }
}
