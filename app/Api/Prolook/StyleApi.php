<?php

namespace App\Api\Prolook;

use App\Models\Cut;
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

    public function getByGroupCutId($group_cut_id)
    {
        $styles = new \stdClass;
        $styles->styles = [];
        $styles->success = false;

        $cuts = Cut::findBy("group_cut_id", $group_cut_id)->get();

        if ($cuts->isNotEmpty())
        {
            $cut_ids = $cuts->pluck("cut_id")->toArray();

            if (!empty($cut_ids))
            {
                $stylesArr = [];

                foreach ($cut_ids as $id)
                {
                    $result = $this->getByCutId($id, true);

                    if ($result->success)
                    {
                        $stylesArr[] = $result->lookup_to_styles;
                    }
                }

                $styles->success = true;
                $styles->styles = array_flatten($stylesArr);
            }
        }

        return $styles;
    }

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
                        unset($cutStyle->alias);
                        unset($cutStyle->created_at);
                        // unset($cutStyle->cut_id);
                        unset($cutStyle->deleted_at);
                        unset($cutStyle->gender);
                        unset($cutStyle->id);
                        unset($cutStyle->style_category);
                        unset($cutStyle->updated_at);

                        $styleInfo = $this->getInfo($cutStyle->style_id);

                        if ($styleInfo->success)
                        {
                            if (isset($styleInfo->material))
                            {
                                $material = $styleInfo->material;

                                $cutStyle->info = new \stdClass;
                                $cutStyle->info->name = $material->name;
                                $cutStyle->info->thumbnail_path = $material->thumbnail_path;
                                $cutStyle->info->sizing_config_prop = $material->sizing_config_prop;
                                $cutStyle->info->neck_option = $material->neck_option;
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
        } catch (ServerException $e) {
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
