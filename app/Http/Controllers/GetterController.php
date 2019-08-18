<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetterController extends Controller
{
    public function getAuthenticatedUser(Request $request)
    {
        $user = $request->user();

        $userData = $user->toArray();
        unset($userData['created_at']);
        unset($userData['updated_at']);
        unset($userData['api_token']);

        return response()->json([
            'success' => true,
            'user' => $userData
        ]);
    }
}
