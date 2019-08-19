<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthUserController extends Controller
{
    public function getAuthenticatedUser(Request $request)
    {
        $user = $request->user();

        $userData = $user->toArray();
        unset($userData['created_at']);
        unset($userData['updated_at']);
        unset($userData['api_token']);

        return response()->json([
            'success' => true,
            'user' => $userData
        ]);
    }

    public function getCurrentCart(Request $request)
    {
        $user = $request->user();

        $cart = $user->carts()
                    ->validToUse()
                    ->get()
                    ->last();

        return response()->json(
            !is_null($cart) ?
            [
                'success' => true,
                'cart_token' => $cart->token
            ] :
            [
                'success' => false,
                'message' => "No current cart"
            ]
        );
    }
}