<?php

namespace App\Api\Pdf;

use GuzzleHttp\Client;
use Webmozart\Json\JsonDecoder;

class Api extends Client
{
    protected $decoder;

    public function __construct($access_token=null)
    {
        $settings = [
            'base_uri' => config("pdf.api_host")
        ];

        if (!is_null($access_token))
        {
            $settings['headers'] = [
                'Authorization' => "Bearer {$access_token}"
            ];
        }

        parent::__construct($settings);
        $this->decoder = new JsonDecoder();
    }
}
