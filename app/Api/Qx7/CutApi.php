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

    public function getAllByBrand($brand)
    {
        $result = $this->getAll();

        if ($result->success)
        {
            $cutsObj = $result->master_3d_block_patterns;

            $cuts = new \stdClass;
            $cuts->success = true;
            $cuts->master_3d_block_patterns = [];

            foreach ($cutsObj as $cutObj)
            {
                if (isset($cutObj->brand))
                {
                    if (strtolower($cutObj->brand->brand) === $brand)
                    {
                        array_push($cuts->master_3d_block_patterns, [
                            'id' => $cutObj->id,
                            'block_pattern_name' => $cutObj->block_pattern_name,
                            'image_thumbnail' => $cutObj->image_thumbnail,
                            'sport' => $cutObj->sport->sport_name,
                            'brand' => $brand
                        ]);
                    }
                }
            }

            return $cuts;
        }

        return null;
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
