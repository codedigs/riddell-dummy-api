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

        return response()->json([
            'success' => true,
            'user' => $userData
        ]);
    }

    public function getCarts(Request $request)
    {
        $user = $request->user();
        $carts = $user->carts->toArray();

        $filter_carts = array_map(function($cart) {
            return [
                'pl_cart_id' => $cart['pl_cart_id'],
                'is_active' => $cart['is_active'],
                'is_completed' => $cart['is_completed'],
                'is_abandoned' => $cart['is_abandoned']
            ];
        }, $carts);

        return response()->json([
            'success' => true,
            'carts' => $filter_carts
        ]);
    }
}
