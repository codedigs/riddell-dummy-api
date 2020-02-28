<?php

namespace App\Api\Prolook;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class UserApi extends Api
{
    public function quickRegistration($email, $is_from_hybris=0)
    {
        $riddell_brand_id = config("riddell.brand_id");

        try {
            $response = $this->post("api/user/riddell/quickRegistration", [
                'json' => compact("email", "riddell_brand_id", "is_from_hybris"),
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

    public function isEmailAvailable($email)
    {
        try {
            $response = $this->post("api/user/isEmailAvailable", [
                'json' => compact("email")
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

    public function bindToRiddell($email)
    {
        try {
            $response = $this->post("api/user/brandings/bind/riddell", [
                'json' => compact("email")
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
