<?php

namespace App\Api\Prolook;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class StyleApi extends Api
{
    // public function getAll()
    // {
    //     try {
    //         $response = $this->get("api/lookup_cut_to_styles");
    //         return $this->decoder->decode($response->getBody());
    //     } catch (ClientException $e) {
    //         $response = $e->getResponse();

    //         $result = new \stdClass;
    //         $result->success = false;
    //         $result->status_code = $response->getStatusCode();
    //         $result->message = $response->getReasonPhrase();

    //         return $result;
    //     }
    // }

    public function getByCutId($cut_id)
    {
        try {
            $response = $this->get("api/lookup_cut_to_style/cut_id/{$id}");
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
