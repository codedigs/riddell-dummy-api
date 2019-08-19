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
}
