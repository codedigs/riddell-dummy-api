<?php

namespace App\Api\Clients;

use App\Api\ProlookApi;
use App\Api\Qx7Api;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class StyleApi extends ProlookApi
{
    public function getAll()
    {
        try {
            $response = $this->get("api/lookup_cut_to_styles");
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

    public function getById($id)
    {
        try {
            $response = $this->get("api/lookup_cut_to_style/{$id}");
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
