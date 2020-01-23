<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItemSide2 extends Model
{
    use SoftDeletes;

    protected $table = "cart_items_side2";
    protected $fillable = ["style_id"];

    const NO_IMAGE_PLACEHOLDER = "https://via.placeholder.com/1000x1100?text=No%20Image";

    public function getFrontThumbnail($placeholder=true)
    {
        $image = $this->front_image;

        if (is_null($image) && $placeholder)
        {
            $image = static::NO_IMAGE_PLACEHOLDER;
        }

        return $image;
    }

    public function getBackThumbnail($placeholder=true)
    {
        $image = $this->back_image;

        if (is_null($image) && $placeholder)
        {
            $image = static::NO_IMAGE_PLACEHOLDER;
        }

        return $image;
    }

    public function getLeftThumbnail($placeholder=true)
    {
        $image = $this->left_image;

        if (is_null($image) && $placeholder)
        {
            $image = static::NO_IMAGE_PLACEHOLDER;
        }

        return $image;
    }

    public function getRightThumbnail($placeholder=true)
    {
        $image = $this->right_image;

        if (is_null($image) && $placeholder)
        {
            $image = static::NO_IMAGE_PLACEHOLDER;
        }

        return $image;
    }
}
