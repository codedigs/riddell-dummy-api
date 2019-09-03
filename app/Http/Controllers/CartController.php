<?php

namespace App\Http\Controllers;

use App\Api\Riddell\CartApi;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function syncToHybris(Request $request)
    {
        $user = $request->user();
        $currentCart = $user->getCurrentCart();

        $rows = $currentCart->getCartItemsByHybrisFormat();

        $cartApi = new CartApi($user->hybris_access_token);
        $result = $cartApi->update($currentCart->pl_cart_id, $user->user_id, $user->email, $rows);

        // convert result to array
        $result = json_decode(json_encode($result), true);

        return response()->json($result);
    }
}
