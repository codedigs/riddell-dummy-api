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

    public function getByCutId($cut_id, $spread=false)
    {
        try {
            $response = $this->get("api/lookup_cut_to_style/cut_id/{$cut_id}");
            $result = $this->decoder->decode($response->getBody());

            if ($result->success)
            {
                if ($spread)
                {
                    $cutStyles = $result->lookup_to_styles;

                    foreach ($cutStyles as $cutStyle)
                    {
                        $styleInfo = $this->getInfo($cutStyle->style_id);

                        if ($styleInfo->success)
                        {
                            if (isset($styleInfo->material))
                            {
                                $material = $styleInfo->material;

                                $cutStyle->info = new \stdClass;
                                $cutStyle->info->name = $material->name;
                                $cutStyle->info->thumbnail_path = $material->thumbnail_path;
                            }
                        }
                    }
                }
            }

            return $result;
        } catch (ClientException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        }
    }

    public function getInfo($style_id)
    {
        try {
            $response = $this->get("api/material/{$style_id}");
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
