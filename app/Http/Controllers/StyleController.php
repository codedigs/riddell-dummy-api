<?php

namespace App\Http\Controllers;

use App\Models\Style;

class StyleController extends Controller
{
    public function getStyles()
    {
        $designs = Style::all();

        return response()->json([
            'success' => true,
            'data' => $designs->toArray()
        ]);
    }
}
