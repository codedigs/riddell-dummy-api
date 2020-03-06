<?php

namespace App\Http\Controllers;

use App\Api\Prolook\StyleApi;
use App\Api\Qx7\GroupCutApi;

class StyleController extends Controller
{
    public function getStylesByGroupCutId($group_cut_id)
    {
        $styles = new \stdClass;
        $styles->styles = [];
        $styles->success = false;

        $groupCutApi = new GroupCutApi;
        $groupCut = $groupCutApi->getGroupStylesById($group_cut_id);

        if ($groupCut->success)
        {
            if (isset($groupCut->master_block_pattern_group))
            {
                $blockPatterns = $groupCut->master_block_pattern_group->block_patterns;

                foreach ($blockPatterns as $blockPattern)
                {
                    if (!empty($blockPattern->skus))
                    {
                        $skus = $blockPattern->skus;

                        foreach ($skus as $sku)
                        {
                            if (!in_array($sku->style_id, array_column($styles->styles, "style_id")))
                            {
                                array_push($styles->styles, [
                                    'style_id' => $sku->style_id,
                                    'info' => [
                                        'name' => $sku->style_name,
                                        'thumbnail_path' => $sku->thumbnail_path,
                                        'neck_option' => $sku->neck_option
                                    ]
                                ]);
                            }
                        }
                    }
                }

                $styles->success = true;
                return response()->json($styles);
            }
        }

        return response()->json([
            'success' => false,
            'message' => $groupCut->message
        ]);
    }

    public function getStylesByCutId($cut_id)
    {
        $styleApi = new StyleApi;

        $result = json_encode($styleApi->getByCutId($cut_id, true));
        $result = json_decode($result, true); // convert to array

        return response()->json($result);
    }
}
