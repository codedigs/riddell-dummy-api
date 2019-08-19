<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\CoachRequestLog;
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

    /**
     * Add log on cart item
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - cart_token
     * - cut_note
     * - style_note
     * - customizer_note
     * - roster_note
     * - application_size_note
     *
     * @param Request $request
     */
    public function store(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        $params = $request->all();

        if (isset($params['cut_note'], $params['style_note'], $params['customizer_note'], $params['roster_note'], $params['application_size_note']))
        {
            if (!empty($params['cut_note']) ||
                !empty($params['style_note']) ||
                !empty($params['customizer_note']) ||
                !empty($params['roster_note']) ||
                !empty($params['application_size_note']))
            {
                $result = $cartItem->coach_request_logs()->create([
                    'cut_note' => $params['cut_note'],
                    'style_note' => $params['style_note'],
                    'customizer_note' => $params['customizer_note'],
                    'roster_note' => $params['roster_note'],
                    'application_size_note' => $params['application_size_note']
                ]);

                if ($result instanceof CoachRequestLog)
                {
                    $cartItem->markAsCoachHasChangeRequest();

                    return response()->json([
                        'success' => true,
                        'message' => "Successfully create log"
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => "Cannot create log this time. Please try again later."
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot create log without any value on cut, style, customizer, roster and application size"
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => "cut_note, style_note, customizer_note, roster_note, and application_size_note must be define."
        ]);
    }
}
