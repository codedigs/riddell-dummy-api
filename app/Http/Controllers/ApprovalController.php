<?php

namespace App\Http\Controllers;

use App\Api\Prolook\StyleApi;
use App\Api\Qx7\GroupCutApi;
use App\Models\ClientInformation;
use Illuminate\Http\Request;
use Validator;

/**
 * Dependency
 *  - Approval Middleware
 *  - ApprovalCartItem Middleware
 */
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
        $currentCart = $cartItem->cart;

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

        if (!is_null($cartItem->getCutId()))
        {
            $groupCutApi = new GroupCutApi;
            $groupCutResult = $groupCutApi->getById($cartItem->cut_id);

            if ($groupCutResult->success)
            {
                $groupCut = $groupCutResult->master_block_pattern_group;

                $cartItem['group_cut'] = [
                    'id' => $groupCut->id,
                    'name' => $groupCut->name,
                    'image' => !is_null($groupCut->thumbnail) ? $groupCut->thumbnail : "/riddell/img/Cuts/cut-7.png"
                ];
            }
        }

        if (!is_null($cartItem->getStyleId()))
        {
            $styleApi = new StyleApi;
            $style = $styleApi->getInfo($cartItem->getStyleId());

            if ($style->success)
            {
                $material = $style->material;

                $cartItem['style'] = [
                    'id' => $material->id,
                    'name' => $material->name,
                    'image' => !empty($material->thumbnail_path) ? $material->thumbnail_path : "/riddell/img/Football-Picker/Inspiration@2x.png"
                ];
            }
        }

        return response()->json([
            'success' => true,
            'is_cart_available' => !$currentCart->isCompleted(),
            'ready_to_submit' => $currentCart->areAllItemsApproved(),
            'data' => $clientInfo
        ]);
    }

    public function updateRoster(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();

        $params = $request->all();

        $validator = Validator::make($params, [
            'roster' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = $clientInfo->cart_item;
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

    public function updateClientInformation(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();

        $params = $request->all();

        $modifiedRules = ClientInformation::$rules;
        unset($modifiedRules['email']);

        $validator = Validator::make($params, $modifiedRules);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        // update client information
        $clientInfo->school_name = isset($params['school_name']) ? $params['school_name'] : "";
        $clientInfo->first_name = $params['first_name'];
        // $clientInfo->last_name = $params['last_name'];
        // $clientInfo->business_phone = isset($params['business_phone']) ? $params['business_phone'] : "";
        // $clientInfo->address_1 = isset($params['address_1']) ? $params['address_1'] : "";
        // $clientInfo->address_2 = isset($params['address_2']) ? $params['address_2'] : "";
        // $clientInfo->city = isset($params['city']) ? $params['city'] : "";
        // $clientInfo->state = isset($params['state']) ? $params['state'] : "";
        // $clientInfo->zip_code = isset($params['zip_code']) ? $params['zip_code'] : "";

        $saved = $clientInfo->save();

        return response()->json(
            $saved ?
            [
                'success' => true,
                'message' => "Successfully update client information"
            ] :
            [
                'success' => false,
                'message' => "Cannot update client information this time. Please try again later."
            ]
        );
    }

    public function updateSignatureImage(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();

        $params = $request->all();

        $validator = Validator::make($params, [
            'signature_image' => "required|url|max:255",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = $clientInfo->cart_item;
        $cartItem->signature_image = $params['signature_image'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update signature image"
            ] :
            [
                'success' => false,
                'message' => "Cannot update signature image this time. Please try again later."
            ]
        );
    }

    public function markAsApproved(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();
        $cartItem = $clientInfo->cart_item;

        return response()->json(
            $cartItem->markAsApproved() ?
            [
                'success' => true,
                'message' => "Successfully approved"
            ] :
            [
                'success' => false,
                'message' => "Cannot approved this time. Please try again later."
            ]
        );
    }

    public function getBuilderCustomization(Request $request)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $this->approval_token)->first();
        $cartItem = $clientInfo->cart_item;

        return response()->json([
            'success' => true,
            'builder_customization' => json_decode($cartItem->builder_customization, true)
        ]);
    }
}
