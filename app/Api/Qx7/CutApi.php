<?php

namespace App\Api\Qx7;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class CutApi extends Api
{
    public function getAll()
    {
        try {
            $response = $this->get("api/master_3d_block_patterns");
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
            $response = $this->get("api/master_3d_block_pattern/{$id}");
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
