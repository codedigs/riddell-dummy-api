<?php

namespace App\Http\Controllers;

use App\Models\ClientInformation;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function getClientInformation($approval_token)
    {
        $clientInfo = ClientInformation::findBy('approval_token', $approval_token)->first();

        unset($clientInfo['created_at']);
        unset($clientInfo['updated_at']);

        return response()->json([
            'success' => true,
            'data' => $clientInfo
        ]);
    }
}
