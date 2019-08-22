<?php

namespace App\Models;

use App\Api\Clients\StyleApi;
use Illuminate\Database\Eloquent\Model;
use Log;

class Style extends Model
{
    protected $fillable = ["image", "name"];

    public function getImage()
    {
        return $this->image;
    }

    public function getName()
    {
        return $this->name;
    }

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
