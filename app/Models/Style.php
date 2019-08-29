<?php

namespace App\Models;

use App\Api\Prolook\StyleApi;
use Illuminate\Database\Eloquent\Model;
use Log;

class Style extends Model
{
    public static function getByCutId($cut_id)
    {
        $styleApi = new StyleApi;
        $result = $styleApi->getByCutId($style_id);

        if ($result->success)
        {
            if (isset($result->lookup_to_styles))
            {
                return $result->lookup_to_styles;
            }
        }

        Log::error("Error: " . $result->message);
        return null;
    }
}
