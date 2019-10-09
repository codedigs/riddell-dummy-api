<?php

namespace App\Api\Riddell;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class CartApi extends Api
{
    /**
     * data format to be post
     *
        {
            'prolook_order_id' => $currentCart->pl_cart_id,
            'hybrisCartCode' => "",
            'repEmail' => $user->email,
            'repName' => "",
            'repPhone' => "",
            'contactName' => "",
            'contactEmail' => "",
            'status' => "PENDING",
            "designs" => [
                [
                    'cutID' => "93",
                    'cutName' => "CutOne",
                    'customizerStyleID' => "5514",
                    'customizerStyleName' => "StyleOne",
                    'designID' => "123456",
                    'designStatus' => "PENDING",
                    'lineItemID' => "123456",
                    'styleDescription' => "CutOne StyleOne",
                    'url' => "http://"
                ]
            ]
        }
     */
    public function update($pl_cart_id, $user_email, $rows)
    {
        // $designs = $line_items;
        // $STATUS = "PENDING";

        // $data = [
        //     'prolook_order_id' => $pl_cart_id,
        //     'hybrisCartCode' => "",
        //     'repEmail' => $user_email,
        //     'repName' => "",
        //     'repPhone' => "",
        //     'contactName' => "",
        //     'contactEmail' => "",
        //     'status' => $STATUS,
        //     "designs" => $designs
        // ];

        // return compact("pl_cart_id", "rows"); // temporary

        try {
            $response = $this->put("api/customizer/cartupdate", [
                'json' => compact("pl_cart_id", "rows")
                // 'json' => $data
            ]);

            return $this->decoder->decode($response->getBody());
        } catch (ClientException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        } catch (ServerException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        }
    }

    public function submitOrder($data)
    {
        try {
            $response = $this->post("/api/customizer/submitcart", [
                'json' => $data
            ]);

            return $this->decoder->decode($response->getBody());
        } catch (ClientException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        } catch (ServerException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        }
    }
}
