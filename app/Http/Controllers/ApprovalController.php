<?php

namespace App\Http\Controllers;

use App\Models\ClientInformation;
use Illuminate\Http\Request;
use Validator;

class ApprovalController extends Controller
{
    private $approval_token;

    public function __construct(Request $request)
    {
        $authorization = $request->header("Authorization");
        list($type, $approval_token) = explode(" ", $authorization);

        $this->approval_token = $approval_token;
    }

    public function getClientInformation()
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();
        $cartItem = $clientInfo->cart_item;

        $cartItem->status = $cartItem->getStatus();

        unset($cartItem['is_approved']);
        unset($cartItem['has_change_request']);
        unset($cartItem['has_pending_approval']);
        unset($cartItem['created_at']);
        unset($cartItem['created_at']);
        unset($cartItem['updated_at']);
        unset($cartItem['deleted_at']);

        unset($clientInfo['created_at']);
        unset($clientInfo['updated_at']);
        unset($clientInfo['cart_item_id']);

        return response()->json([
            'success' => true,
            'data' => $clientInfo
        ]);
    }

    public function updateRoster(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();
        $cartItem = $clientInfo->cart_item;

        $params = $request->all();

        $validator = Validator::make($params, [
            'roster' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem->roster = $params['roster'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update roster"
            ] :
            [
                'success' => false,
                'message' => "Cannot update roster this time. Please try again later."
            ]
        );
    }
}
