<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

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

        $data = $cart->cart_items->toArray();

        return response()->json([
            'success' => true,
            'data' => $data
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
        $cart_token = $request->get('cart_token');
        $cart = Cart::findByToken($cart_token);

        $params = $request->all();

        if (isset($params['design_id'],
            $params['customizer_url'],
            $params['is_approved'],
            $params['has_change_request'],
            $params['has_pending_approval']))
        {
            if (!empty($params['cut_id']))
            {
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

            return response()->json([
                'success' => false,
                'message' => "cut_id is required"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "design_id, customizer_url, is_approved, has_change_request and has_pending_approval must be define."
        ]);
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
        $cartItem = CartItem::find($cart_item_id);

        $cut_id = $request->get('cut_id');

        if (isset($cut_id))
        {
            if (!empty($cut_id))
            {
                $cartItem->cut_id = $cut_id;

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

            return response()->json([
                'success' => false,
                'message' => "Cut id must not empty"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Cut id must be define"
        ]);
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
        $cartItem = CartItem::find($cart_item_id);

        $style_id = $request->get('style_id');

        if (isset($style_id))
        {
            if (!empty($style_id))
            {
                $cartItem->style_id = $style_id;

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

            return response()->json([
                'success' => false,
                'message' => "Style id must not empty"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Style id must be define"
        ]);
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
        $cartItem = CartItem::find($cart_item_id);

        $customizer_url = $request->get('customizer_url');

        if (isset($customizer_url))
        {
            if (!empty($customizer_url))
            {
                $cartItem->customizer_url = $customizer_url;

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

            return response()->json([
                'success' => false,
                'message' => "Customizer url must not empty"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Customizer url must be define"
        ]);
    }
}
