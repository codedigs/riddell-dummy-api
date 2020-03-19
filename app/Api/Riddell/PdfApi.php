<?php

namespace App\Api\Riddell;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class PdfApi extends Api
{
    public function generate($json)
    {
        try {
            $response = $this->post("api/pdf/generate", compact('json'));
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
