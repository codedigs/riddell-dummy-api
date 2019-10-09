<?php

namespace App\Http\Controllers;

use App\Api\Riddell\CartApi;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function save(Request $request)
    {
        $user = $request->user();
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        $rows = $currentCart->getCartItemsByHybrisFormat();

        $cartApi = new CartApi($user->hybris_access_token);
        $result = $cartApi->update($currentCart->pl_cart_id, $user->email, $rows);

        // convert result to array
        $result = json_decode(json_encode($result), true);

        return response()->json($result);
    }
}
