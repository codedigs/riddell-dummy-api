<?php

namespace App\Models;

use App\Api\Clients\CutApi;
use Illuminate\Database\Eloquent\Model;
use Log;

class Cut extends Model
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

    public static function getById($cut_id)
    {
        $cutApi = new CutApi;
        $result = $cutApi->getById($cut_id);

        if ($result->success)
        {
            if (isset($result->master_3d_block_patterns))
            {
                return $result->master_3d_block_patterns;
            }
        }

        Log::error("Error: " . $result->message);
        return null;
    }
}
