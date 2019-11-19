<?php

namespace App\Http\Controllers;

use App\Api\Qx7\GroupCutApi;

class GroupCutController extends Controller
{
    public function getAll()
    {
        $groupCutApi = new GroupCutApi;

        $result = json_encode($groupCutApi->getAll());
        $result = json_decode($result, true); // convert to array

        return response()->json($result);
    }
}
