<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachRequestLog extends Model
{
    protected $fillable = ["cut_note", "style_note", "customizer_note", "roster_note", "application_size_note", "cart_item_id"];
}