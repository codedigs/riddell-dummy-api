<?php

namespace App\Http\Controllers;

use App\Models\Design;

class DesignController extends Controller
{
    public function getDesigns()
    {
        $designs = Design::all();

        return response()->json([
            'success' => true,
            'data' => $designs->toArray()
        ]);
    }
}
