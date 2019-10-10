<?php

namespace App\Http\Controllers;

use App\Models\Cut;
use App\Transformers\CutTransformer;

use App\Api\Qx7\CutApi;

class CutController extends Controller
{
    public function getAll()
    {
        if (config("app.use_cuts_in_db"))
        {
            $cuts = transformer(Cut::all(), new CutTransformer)->toArray();

            return response()->json([
                'success' => true,
                'lookup_to_styles' => $cuts['data']
            ]);
        }

        $cutApi = new CutApi;

        $result = json_encode($cutApi->getAllByBrand());
        $result = json_decode($result, true); // convert to array

        return response()->json($result);
    }
}
