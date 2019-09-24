<?php

namespace App\Api\Prolook;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use App\Api\Qx7\CutApi as Qx7CutApi;

class CutApi extends Api
{
    public function getAll($spread=false)
    {
        try {
            $response = $this->get("api/lookup_cut_to_styles");
            $result = $this->decoder->decode($response->getBody());

            if ($result->success)
            {
                if ($spread)
                {
                    $cutStyles = $result->lookup_to_styles;
                    $qx7CutApi = new Qx7CutApi;

                    foreach ($cutStyles as $cutStyle)
                    {
                        unset($cutStyle->created_at);
                        unset($cutStyle->updated_at);
                        unset($cutStyle->deleted_at);

                        $cutInfo = $qx7CutApi->getById($cutStyle->cut_id);

                        if ($cutInfo->success)
                        {
                            if (isset($cutInfo->master_3d_block_patterns))
                            {
                                $block_pattern = $cutInfo->master_3d_block_patterns;

                                $cutStyle->cutInfo = new \stdClass;
                                $cutStyle->cutInfo->name = $block_pattern->block_pattern_name;
                                $cutStyle->cutInfo->image = $block_pattern->image_thumbnail;
                                $cutStyle->cutInfo->sport = $block_pattern->sport->sport_name;
                            }
                        }

                        //     // 'id' => $cutObj->id,
                        //     // 'block_pattern_name' => $cutObj->block_pattern_name,
                        //     // 'image_thumbnail' => $cutObj->image_thumbnail,
                        //     // 'sport' => $cutObj->sport->sport_name,
                        //     // 'brand' => $brand
                        // }
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
        } catch (ServerException $e) {
            $response = $e->getResponse();

            $result = new \stdClass;
            $result->success = false;
            $result->status_code = $response->getStatusCode();
            $result->message = $response->getReasonPhrase();

            return $result;
        }
    }

    // public function getAllByBrand($brand)
    // {
    //     $result = $this->getAll();

    //     if ($result->success)
    //     {
    //         $cutsObj = $result->master_3d_block_patterns;

    //         $cuts = new \stdClass;
    //         $cuts->success = true;
    //         $cuts->master_3d_block_patterns = [];

    //         foreach ($cutsObj as $cutObj)
    //         {
    //             if (isset($cutObj->brand))
    //             {
    //                 if (strtolower($cutObj->brand->brand) === $brand)
    //                 {
    //                     array_push($cuts->master_3d_block_patterns, [
    //                         'id' => $cutObj->id,
    //                         'block_pattern_name' => $cutObj->block_pattern_name,
    //                         'image_thumbnail' => $cutObj->image_thumbnail,
    //                         'sport' => $cutObj->sport->sport_name,
    //                         'brand' => $brand
    //                     ]);
    //                 }
    //             }
    //         }

    //         return $cuts;
    //     }

    //     return null;
    // }

    // public function getById($id)
    // {
    //     try {
    //         $response = $this->get("api/master_3d_block_pattern/{$id}");
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
}
