<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ChangeLog;
use App\Rules\MultipleUrl;
use Illuminate\Http\Request;
use Validator;

class ChangeLogController extends Controller
{
    /**
     * Get logs of cart item
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function getAll(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        $logs = $cartItem->changes_logs->toArray();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Add ask for change log
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - note
     * - attachments
     *
     * @param Request $request
     */
    public function askForChange(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'note' => "required|string|max:255",
            'attachments' => ["required", new MultipleUrl]
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $result = ChangeLog::createAskForChanges($params['note'], $params['attachments'], $cart_item_id);

        if ($result instanceof ChangeLog)
        {
            $cartItem = CartItem::find($cart_item_id);
            $cartItem->markAsHasChangeRequest();

            return response()->json([
                'success' => true,
                'message' => "Successfully create log for 'ask for changes'"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Cannot create log for 'ask for changes' this time. Please try again later."
        ]);
    }
}
