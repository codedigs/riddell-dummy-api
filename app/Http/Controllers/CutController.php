<?php

namespace App\Http\Controllers;

use App\Api\Prolook\CutApi;
// use App\Api\Qx7\CutApi;

class CutController extends Controller
{
    public function getAll()
    {
        $cutApi = new CutApi;

        $result = json_encode($cutApi->getAll(true));
        $result = json_decode($result, true); // convert to array

        // $result = json_encode($cutApi->getAllByBrand());
        // $result = json_decode($result, true); // convert to array

        return response()->json($result);
    }
}
