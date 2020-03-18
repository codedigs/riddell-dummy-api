<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Log;

class RosterController extends Controller
{
    /**
     * Delete adult roster
     *
     * @param Request $request
     */
    public function deleteAdultRoster(Request $request, $pl_cart_id, $line_item_id)
    {
        $cart = Cart::findBy('pl_cart_id', $pl_cart_id)->first();

        if (!is_null($cart))
        {
            $line_item_ids = $cart->cart_items->pluck("line_item_id")->toArray();

            if (in_array($line_item_id, $line_item_ids))
            {
                $cartItem = CartItem::findBy("line_item_id", $line_item_id)->first();

                return response()->json(
                    $cartItem->deleteAdultRoster() ? [
                        'success' => true,
                        'message' => "Successfully deleted adult roster.",
                        'status_code' => 200
                    ] : [
                        'success' => true,
                        'message' => "Cannot delete adult roster this time. Please try again later.",
                        'status_code' => 200
                    ]
                );
            }
            else
            {
                Log::warning("Warning: Line item id {$line_item_id} not belong to Pl cart id {$pl_cart_id}!");
            }
        }
        else
        {
            Log::warning("Warning: Pl cart id {$pl_cart_id} is not exist!");
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart",
            'status_code' => 401
        ]);
    }

    /**
     * Delete youth roster
     *
     * @param Request $request
     */
    public function deleteYouthRoster(Request $request, $pl_cart_id, $line_item_id)
    {
        $cart = Cart::findBy('pl_cart_id', $pl_cart_id)->first();

        if (!is_null($cart))
        {
            $line_item_ids = $cart->cart_items->pluck("line_item_id")->toArray();

            if (in_array($line_item_id, $line_item_ids))
            {
                $cartItem = CartItem::findBy("line_item_id", $line_item_id)->first();

                return response()->json(
                    $cartItem->deleteYouthRoster() ? [
                        'success' => true,
                        'message' => "Successfully deleted youth roster.",
                        'status_code' => 200
                    ] : [
                        'success' => true,
                        'message' => "Cannot delete youth roster this time. Please try again later.",
                        'status_code' => 200
                    ]
                );
            }
            else
            {
                Log::warning("Warning: Line item id {$line_item_id} not belong to Pl cart id {$pl_cart_id}!");
            }
        }
        else
        {
            Log::warning("Warning: Pl cart id {$pl_cart_id} is not exist!");
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart",
            'status_code' => 401
        ]);
    }
}
