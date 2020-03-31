<?php

namespace App\Api\Pdf;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class PdfApi extends Api
{
    public function upload($json)
    {
        try {
            $response = $this->post("api/upload", compact('json'));
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
