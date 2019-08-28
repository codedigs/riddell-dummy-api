<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Transformers\CartItemTransformer;
use Illuminate\Http\Request;
use Validator;

class CartItemController extends Controller
{
    /**
     * Get items of cart
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *
     * Data available
     * - cart_token
     *
     * @param Request $request
     */
    public function getCartItems(Request $request)
    {
        $cart_token = $request->get('cart_token');
        $cart = Cart::findByToken($cart_token);

        $cartItems = transformer($cart->cart_items, new CartItemTransformer)->toArray();

        return response()->json([
            'success' => true,
            'data' => $cartItems['data']
        ]);
    }

    /**
     * Get items of cart
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
    public function show(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);
        $itemStatus = $cartItem->getStatus();

        $cartItemData = $cartItem->toArray();
        $cartItemData['status'] = $itemStatus;

        unset($cartItemData['is_approved']);
        unset($cartItemData['has_change_request']);
        unset($cartItemData['has_pending_approval']);
        unset($cartItemData['cart_id']);
        unset($cartItemData['created_at']);
        unset($cartItemData['updated_at']);

        return response()->json([
            'success' => true,
            'data' => $cartItemData
        ]);
    }

    /**
     * Add item to cart
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *
     * Data available
     * - cart_token
     * - cut_id
     * - style_id (optional)
     * - design_id (optional)
     * - is_approved (optional)
     * - has_change_request (optional)
     * - has_pending_approval (optional)
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'cut_id' => "required|numeric",
            'style_id' => "numeric",
            'design_id' => "numeric",
            'is_approved' => "boolean",
            'has_change_request' => "boolean",
            'has_pending_approval' => "boolean"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cart_token = $request->get('cart_token');
        $cart = Cart::findByToken($cart_token);

        $result = $cart->cart_items()->create([
            'cut_id' => $params['cut_id'],
            'style_id' => isset($params['style_id']) ? $params['style_id'] : null,
            'design_id' => isset($params['design_id']) ? $params['design_id'] : null,
            'is_approved' => isset($params['is_approved']) ? $params['is_approved'] : 0,
            'has_change_request' => isset($params['has_change_request']) ? $params['has_change_request'] : 0,
            'has_pending_approval' => isset($params['has_pending_approval']) ? $params['has_pending_approval'] : 0
        ]);

        return response()->json(
            $result instanceof CartItem ?
            [
               'success' => true,
               'message' => "Successfully create cart item",
               'new_cart_item_id' => $result->id
            ] :
            [
               'success' => false,
               'message' => "Cannot create cart item this time. Please try again later."
            ]
        );
    }

    /**
     * Update cut id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - cart_token
     * - cut_id
     *
     * @param Request $request
     */
    public function updateCutId(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'cut_id' => "required|numeric"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->cut_id = $params['cut_id'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update cut id"
            ] :
            [
                'success' => false,
                'message' => "Cannot update cut id this time. Please try again later."
            ]
        );
    }

    /**
     * Update style id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - cart_token
     * - style_id
     *
     * @param Request $request
     */
    public function updateStyleId(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'style_id' => "required|numeric"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->style_id = $params['style_id'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update style id"
            ] :
            [
                'success' => false,
                'message' => "Cannot update style id this time. Please try again later."
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
     * - cart_token
     * - design_id
     *
     * @param Request $request
     */
    public function updateDesignId(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'design_id' => "required|numeric"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->design_id = $params['design_id'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update design id"
            ] :
            [
                'success' => false,
                'message' => "Cannot update design id this time. Please try again later."
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
     * - cart_token
     * - front_image
     * - back_image
     * - left_image
     * - right_image
     *
     * @param Request $request
     */
    public function updateThumbnails(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'front_image' => "required|url",
            'back_image' => "required|url",
            'left_image' => "required|url",
            'right_image' => "required|url",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->front_image = $params['front_image'];
        $cartItem->back_image = $params['back_image'];
        $cartItem->left_image = $params['left_image'];
        $cartItem->right_image = $params['right_image'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update thumbnails"
            ] :
            [
                'success' => false,
                'message' => "Cannot update thumbnails this time. Please try again later."
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
     * - cart_token
     * - application_size
     *
     * @param Request $request
     */
    public function updateApplicationSize(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'application_size' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->application_size = $params['application_size'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update application size"
            ] :
            [
                'success' => false,
                'message' => "Cannot update application size this time. Please try again later."
            ]
        );
    }

    /**
     * Mark as approved
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
    public function approved(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $cartItem = CartItem::find($cart_item_id);

        return response()->json(
            $cartItem->approved() ?
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

    /**
     * Delete cart item
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
    public function delete(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);
        $is_deleted = $cartItem->delete();

        return response()->json(
            $is_deleted ?
            [
                'success' => true,
                'message' => "Successfully delete item"
            ] :
            [
                'success' => false,
                'message' => "Cannot delete item this time. Please try again later."
            ]
        );
    }
}
