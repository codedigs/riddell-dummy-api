<?php

namespace App\Models;

use App\Models\Cart;
// use App\Models\CartItemPlayer;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ["cut_id", "design_id", "customizer_url", "is_approved", "has_change_request", "has_pending_approval", "cart_id"];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

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
