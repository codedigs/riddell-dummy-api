<?php

namespace App\Api\Prolook;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class SavedDesignApi extends Api
{
    public function getById($design_id)
    {
        try {
            $response = $this->get("api/saved_design/{$design_id}");
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
