<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Design extends Model
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
}