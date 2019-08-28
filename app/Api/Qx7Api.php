<?php

namespace App\Api;

use GuzzleHttp\Client;
use Webmozart\Json\JsonDecoder;

class Qx7Api extends Client
{
    protected $decoder;

    public function __construct($access_token=null)
    {
        $settings = [
            'base_uri' => config("qx7.api_host")
        ];

        parent::__construct($settings);
        $this->decoder = new JsonDecoder;
    }
}
