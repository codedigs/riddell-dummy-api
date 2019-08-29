<?php

namespace App\Api\Riddell;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class UserApi extends Api
{
    public function getUserCart()
    {
        try {
            $response = $this->get("api/auth/validate");
            return $this->decoder->decode($response->getBody());
        } catch (ClientException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        }
    }
}
