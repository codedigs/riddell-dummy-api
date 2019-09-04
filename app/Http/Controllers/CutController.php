<?php

namespace App\Http\Controllers;

use App\Api\Qx7\CutApi;

class CutController extends Controller
{
    public function getAllByBrand($brand)
    {
        $cutApi = new CutApi;

        $result = json_encode($cutApi->getAllByBrand($brand));
        $result = json_decode($result, true); // convert to array

        return response()->json($result);
    }
}
