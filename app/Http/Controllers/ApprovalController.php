<?php

namespace App\Http\Controllers;

use App\Models\ClientInformation;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function getClientInformation($approval_token)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $approval_token)->first();
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
}
