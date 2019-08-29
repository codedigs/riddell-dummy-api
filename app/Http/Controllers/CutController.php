<?php

namespace App\Http\Controllers;

use App\Models\Cut;

class CutController extends Controller
{
    public function getCuts()
    {
        $cuts = Cut::all();

        return response()->json([
            'success' => true,
            'data' => $cuts->toArray()
        ]);
    }
}
