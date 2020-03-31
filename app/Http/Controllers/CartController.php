<?php

namespace App\Http\Controllers;

use App\Api\Pdf\PdfApi;
use App\Api\Riddell\CartApi;
use App\Mail\OrderData;
use App\Models\Cart;
use GuzzleHttp\Client;
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

        $rows = $currentCart->getCartItemsByHybrisFormat();

        $cartApi = new CartApi($user->hybris_access_token);
        $result = $cartApi->update($currentCart->pl_cart_id, $user->email, $rows);

        // convert result to array
        $result = json_decode(json_encode($result), true);

        if ($result['success'])
        {
            $items = $currentCart->cart_items;

            foreach ($items as $item)
            {
                if (!$item->hasPdfUrl())
                {
                    $failed_counter = 0;
                    $FAILED_LIMIT = 3;

                    do
                    {
                        $pdfApi = new PdfApi($user->hybris_access_token);
                        $generatePdfResponse = $pdfApi->generate($item->getPdfJson());

                        if ($generatePdfResponse->success)
                        {
                            $item->updatePdfUrl($generatePdfResponse->pdfUrl);
                        }
                        else
                        {
                            Log::error("Error: Generating pdf failed.");
                            $failed_counter++;
                        }
                    } while ($failed_counter < $FAILED_LIMIT);

                    if ($failed_counter === $FAILED_LIMIT)
                    {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot submit order this time. Please try again later."
                        ]);
                    }
                }
            }

            $data = $currentCart->getCartItemsByOrderFormat();

            $client = new Client;
            $prolookResponse = $client->post("https://api.prolook.com/api/order/new", [
                'json' => $data
            ]);

            $prolookResponse = json_decode($prolookResponse->getBody(), 1);

            Log::info("Prolook Response: " . print_r($prolookResponse, true));

            if ($prolookResponse['success'])
            {
                // append pl_cart_id
                $prolookResponse['pl_cart_id'] = $user->current_pl_cart_id;

                $cartApi = new CartApi($user->hybris_access_token);
                $orderResponse = $cartApi->submitOrder2($prolookResponse);

                Log::info("Order Response: " . print_r($orderResponse, true));

                $currentCart->markAsCompleted();

                return response()->json($orderResponse);
            }
            else
            {
                Log::error("Error: Submit order on prolook." . print_r($prolookResponse, true));
            }
        }

        return response()->json($result);
    }

    /**
     * Delete whole cart
     *
     * @param Request $request
     */
    public function deleteWholeCart(Request $request)
    {
        $user = $request->user();
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        Log::debug("current_pl_cart_id " . $user->current_pl_cart_id);
        Log::debug(print_r($currentCart, true));

        if (!is_null($currentCart))
        {
            return response()->json(
                $currentCart->delete() ? [
                    'success' => true,
                    'new_roster' => "Successfully deleted whole cart.",
                    'status_code' => 200
                ] : [
                    'success' => true,
                    'new_roster' => "Cannot delete whole cart this time. Please try again later.",
                    'status_code' => 200
                ]
            );
        }
        else
        {
            Log::warning("Warning: Pl cart id ". $user->current_pl_cart_id ." is not exist!");
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart",
            'status_code' => 401
        ]);
    }

    public function submitData(Request $request)
    {
        $user = $request->user();
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        $data = $currentCart->getCartItemsByOrderFormat();

        return response()->json($data);
    }
}
