<?php

namespace App\Http\Controllers;

use App\Api\Riddell\CartApi;
use App\Mail\OrderData;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Log;

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

    public function submit(Request $request)
    {
        $user = $request->user();
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        $data = $currentCart->getCartItemsByOrderFormat();

        $cartApi = new CartApi($user->hybris_access_token);
        $result = $cartApi->submitOrder($data);

        // convert result to array
        $result = json_decode(json_encode($result), true);

        if ($result['success'])
        {
            $currentCart->markAsCompleted();

            $shipping = array_column($data['order_items'], "shipping");
            $shipping_decode = array_map("json_decode", $shipping);
            $emails = array_column($shipping_decode, "email");

            // send email to jenn after success submitting order if client has email jenn@qstrike.com
            $email = "jenn@qstrike.com";
            if (in_array($email, $emails))
            {
                Log::info("Info: Send order data to jenn.");
                Mail::send(new OrderData($email, $data));
            }
        }

        return response()->json($result);
    }

    public function submitData(Request $request)
    {
        $user = $request->user();
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        $data = $currentCart->getCartItemsByOrderFormat();

        return response()->json($data);
    }
}
