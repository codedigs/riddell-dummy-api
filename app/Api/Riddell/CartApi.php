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
            "pl_cart_id": "f1f4dbfbc6dd",
            "user_id": 4732,
            "user_email": "test@adams.com",
            "rows": [
                {
                    "line_id": "0000490048_ce915",
                    "cut_id": 88,
                    "cut_name": "MOTION, Pant, Cut 1, Elastic Waist",
                    "style_id": 5614,
                    "style_name": "test style name",
                    "design_id": 0,
                    "design_status": "incomplete",
                    "customizer_url": "http://www.test.com/01",
                    "roster": {"sample roster": "sample data"},
                    "active": 1
                }
            ]
        }
     */
    public function update($pl_cart_id, $user_id, $user_email, $line_items)
    {
        $rows = $line_items;

        try {
            $response = $this->put("api/customizer/cartupdate", [
                'json' => compact("pl_cart_id", "user_id", "user_email", "rows")
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
