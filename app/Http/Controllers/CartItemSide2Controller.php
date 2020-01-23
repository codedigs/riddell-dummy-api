<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Validator;

class CartItemSide2Controller extends Controller
{
    /**
     * Update style id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - style_id
     *
     * @param Request $request
     */
    public function updateStyleId(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);
        $has_history_of_changes = $cartItem->changes_logs->isNotEmpty();

        // block request if coach has changes
        if ($has_history_of_changes)
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update style id on side 2 when coach has already change request."
            ]);
        }

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isReviewChanges() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update style id on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'style_id' => "required|numeric|digits_between:1,20"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        if (is_null($cartItem->side2))
        {
            $saved = $cartItem->side2()->create(([
                'style_id' => $params['style_id']
            ]));
        }
        else
        {
            $cartItem->side2->style_id = $params['style_id'];;
            $saved = $cartItem->side2->save();
        }


        return response()->json(
            $saved ?
            [
                'success' => true,
                'message' => "Successfully update style id on side 2."
            ] :
            [
                'success' => false,
                'message' => "Cannot update style id on side 2 this time. Please try again later."
            ]
        );
    }

    /**
     * Update design id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - design_id
     *
     * @param Request $request
     */
    public function updateDesign(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update design on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'design_id' => "required|numeric|digits_between:1,20",
            'builder_customization' => "required|json"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $side2 = $cartItem->side2;
        $side2->design_id = $params['design_id'];
        $side2->builder_customization = $params['builder_customization'];

        return response()->json(
            $side2->save() ?
            [
                'success' => true,
                'message' => "Successfully update design on side 2."
            ] :
            [
                'success' => false,
                'message' => "Cannot update design on side 2 this time. Please try again later."
            ]
        );
    }

    /**
     * Update thumbnails
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - front_image
     * - back_image
     * - left_image
     * - right_image
     *
     * @param Request $request
     */
    public function updateThumbnails(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update thumbnails on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'front_image' => "url|max:255",
            'back_image' => "url|max:255",
            'left_image' => "url|max:255",
            'right_image' => "url|max:255",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $side2 = $cartItem->side2;

        $side2->front_image = $params['front_image'];
        $side2->back_image = $params['back_image'];
        $side2->left_image = $params['left_image'];
        $side2->right_image = $params['right_image'];

        return response()->json(
            $side2->save() ?
            [
                'success' => true,
                'message' => "Successfully update thumbnails on side 2."
            ] :
            [
                'success' => false,
                'message' => "Cannot update thumbnails on side 2 this time. Please try again later."
            ]
        );
    }

    /**
     * Update application size
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - application_size
     *
     * @param Request $request
     */
    public function updateApplicationSize(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update application size on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'application_size' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $side2 = $cartItem->side2;
        $side2->application_size = $params['application_size'];

        return response()->json(
            $side2->save() ?
            [
                'success' => true,
                'message' => "Successfully update application size on side 2."
            ] :
            [
                'success' => false,
                'message' => "Cannot update application size on side 2 this time. Please try again later."
            ]
        );
    }
}
