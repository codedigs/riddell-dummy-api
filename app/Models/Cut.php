<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cut extends Model
{
    protected $fillable = ["cut_id", "hybris_sku", "style_category", "gender", "name", "image", "sport", "group_cut_id"];

    public function scopeFindBy($query, $field, $value)
    {
        return $query->where($field, $value);
    }
}
