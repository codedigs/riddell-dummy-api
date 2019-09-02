<?php

namespace App\Http\Controllers;

use App\Api\Prolook\StyleApi;

class StyleController extends Controller
{
    public function getStylesByCutId($cut_id)
    {
        $styleApi = new StyleApi;

        $result = json_encode($styleApi->getByCutId($cut_id, true));
        $result = json_decode($result, true); // convert to array

        return response()->json($result);
    }
}
