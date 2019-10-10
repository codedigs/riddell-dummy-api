<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cut extends Model
{
    protected $fillable = ["cut_id", "hybris_sku", "style_category", "gender", "name", "image", "sport"];
}
