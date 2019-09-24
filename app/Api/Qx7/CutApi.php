<?php

namespace App\Api\Qx7;

use App\Api\Prolook\StyleApi;
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
        } catch (ServerException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        }
    }

    public function getAllByBrand($brand="riddell")
    {
        $result = $this->getAll();

        if ($result->success)
        {
            $cutsObj = $result->master_3d_block_patterns;

            $cuts = new \stdClass;
            $cuts->success = true;
            $cuts->lookup_to_styles = [];

            $styleApi = new StyleApi;

            foreach ($cutsObj as $cutObj)
            {

                if (isset($cutObj->brand))
                {
                    if (strtolower($cutObj->brand->brand) === $brand)
                    {
                        $styleResponse = $styleApi->getByCutId($cutObj->id);

                        $style_id = 0;
                        $hybris_sku = "";
                        $style_category = "";
                        $gender = "";

                        if ($styleResponse->success)
                        {

                            if (!empty($styleResponse->lookup_to_styles))
                            {
                                $styleObj = $styleResponse->lookup_to_styles[0];

                                if (isset($styleObj->style_id)) $style_id = $styleObj->style_id;
                                if (isset($styleObj->hybris_sku)) $hybris_sku = $styleObj->hybris_sku;
                                if (isset($styleObj->style_category)) $style_category = $styleObj->style_category;
                                if (isset($styleObj->gender)) $gender = $styleObj->gender;
                            }
                        }

                        array_push($cuts->lookup_to_styles, [
                            'cut_id' => $cutObj->id,
                            'style_id' => $style_id,
                            'hybris_sku' => $hybris_sku,
                            'style_category' => $style_category,
                            'gender' => $gender,
                            'cutInfo' => [
                                'name' => $cutObj->block_pattern_name,
                                'image' => $cutObj->image_thumbnail,
                                'sport' => $cutObj->sport->sport_name
                            ]
                        ]);
                    }
                }
            }

            // "id": 9,
            // "cut_id": 75,
            // "style_id": 5591,
            // "hybris_sku": "RHPFBLJA",
            // "alias": null,
            // "style_category": "jerseys",
            // "gender": "men",
            // "cutInfo": {
            //     "name": "LITE, Jersey, Cut 1",
            //     "image": "https://s3.us-west-2.amazonaws.com/qx7/uploaded_files/master_3d_block_patterns/20190905090916/8I23betSmv.png",
            //     "sport": "Football"
            // }

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
