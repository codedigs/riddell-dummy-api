<?php

namespace App\Models;

// use App\Models\Cart;
// use App\Models\CartItemPlayer;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ["material_id", "name", "left_image", "front_image", "back_image", "right_image", "quantity", "brand", "item_id", "block_pattern_id", "neck_option", "description", "type", "builder_customization", "set_group_id", "factory_order_id", "design_sheet", "roster", "applicationType", "additional_attachments", "notes", "cart_id"];

    // public function cart()
    // {
    //     return $this->belongsTo(Cart::class);
    // }

    // public function cart_item_players()
    // {
    //     return $this->hasMany(CartItemPlayer::class);
    // }

    // public function images()
    // {
    //     $left_image = $this->left_image;
    //     $front_image = $this->front_image;
    //     $back_image = $this->back_image;
    //     $right_image = $this->right_image;

    //     return compact('left_image', 'front_image', 'back_image', 'right_image');
    // }

    // public function getDuplicateItem($cart_id)
    // {
    //     return static::where('material_id', $this->material_id)
    //             ->where('cart_id', $cart_id)
    //             ->get();
    // }
}
