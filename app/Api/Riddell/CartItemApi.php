<?php

namespace App\Api\Riddell;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class CartItemApi extends Api
{
    public function syncItem($pl_cart_id, $cut_id)
    {
        try {
            $response = $this->post("api/customizer/lineitem/new", [
                'json' => compact("pl_cart_id", "cut_id")
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
