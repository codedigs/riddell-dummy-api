<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ChangeLog;
use App\Models\ClientInformation;
use App\Rules\MultipleUrl;
use Illuminate\Http\Request;
use Validator;

class ChangeLogController extends Controller
{
    private $approval_token;

    public function __construct(Request $request)
    {
        $authorization = $request->header("Authorization");
        list($type, $approval_token) = explode(" ", $authorization);

        $this->approval_token = $approval_token;
    }

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
    public function getAll(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();
        $cartItem = $clientInfo->cart_item;

        $logs = $cartItem->changes_logs->toArray();

        $filter_logs = array_map(function($log) {
            return [
                'id' => $log['id'],
                'note' => $log['note'],
                'attachments' => $log['attachments'],
                'role' => $log['role'],
                'type' => $log['type'],
                'created_at' => $log['created_at']
            ];
        }, $logs);

        return response()->json([
            'success' => true,
            'logs' => $filter_logs
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
    public function askForChange(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();
        $cartItem = $clientInfo->cart_item;

        // block request if item status was pending approval
        if (!$cartItem->isPendingApproval())
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot create log for 'ask for changes' if status not " . CartItem::STATUS_PENDING_APPROVAL . "."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'note' => "required|string|max:255",
            'attachments' => ["required", new MultipleUrl]
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $result = ChangeLog::createAskForChanges($params['note'], $params['attachments'], $cartItem->id);

        if ($result instanceof ChangeLog)
        {
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
