<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

/**
 * Middleware Dependency:
 *  - CartItemMiddleware
 */
class CartController extends Controller
{
    /**
     * Add cart
     *
     * Dependency
     *  - Authenticate Middleware
     *
     * @param Request $request
     */
    public function addCart(Request $request)
    {
        $user = $request->user();

        if (!$user->hasValidCart())
        {
            $cart = Cart::takeCart();

            $cart->assignToUser($user->id);

            return response()->json(
                $cart instanceof Cart ?
                [
                    'success' => true,
                    'message' => "Successfully create cart",
                    'cart_token' => $cart->token
                ] :
                [
                    'success' => false,
                    'message' => "Cannot create cart this time. Please try again later."
                ]
            );
        }
        else
        {
            $cart = $user->carts()
                        ->validToUse()
                        ->get()
                        ->last();

            return response()->json([
                'success' => false,
                'message' => "You have already cart",
                'cart_token' => $cart->token
            ]);
        }
    }
}
