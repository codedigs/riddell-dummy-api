<?php

namespace App\Http\Controllers;

class CutController extends Controller
{
    public function getCuts()
    {
        return response()->json([
            'success' => true,
            'message' => "Hello World"
        ]);
    }
}
