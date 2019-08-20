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
     * - design_id (optional)
     * - customizer_url (optional)
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
            'design_id' => "numeric",
            'customizer_url' => "url",
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
            'design_id' => isset($params['design_id']) ? $params['design_id'] : null,
            'customizer_url' => isset($params['customizer_url']) ? $params['customizer_url'] : null,
            'is_approved' => isset($params['is_approved']) ? $params['is_approved'] : 0,
            'has_change_request' => isset($params['has_change_request']) ? $params['has_change_request'] : 0,
            'has_pending_approval' => isset($params['has_pending_approval']) ? $params['has_pending_approval'] : 0
        ]);

        return response()->json(
            $result instanceof CartItem ?
            [
               'success' => true,
               'message' => "Successfully create cart item"
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
     * Update customizer url
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - cart_token
     * - customizer_url
     *
     * @param Request $request
     */
    public function updateCustomizerUrl(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'customizer_url' => "required|url"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->customizer_url = $params['customizer_url'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update customizer url"
            ] :
            [
                'success' => false,
                'message' => "Cannot update customizer url this time. Please try again later."
            ]
        );
    }
}
