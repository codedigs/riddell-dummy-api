<?php

namespace App\Models;

use App\Models\Cart;
// use App\Models\CartItemPlayer;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ["cut_id", "design_id", "customizer_url", "is_approved", "has_change_request", "has_pending_approval", "cart_id"];

    const STATUS_REVIEW_CHANGES = "review changes";
    const STATUS_APPROVED = "approved";
    const STATUS_PENDING_APPROVAL = "pending approval";
    const STATUS_GET_APPROVAL = "get approval";
    const STATUS_INCOMPLETE = "incomplete";

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function coach_request_logs()
    {
        return $this->hasMany(CoachRequestLog::class);
    }

    public function getStatus()
    {
        switch(true) {
            case is_null($this->cut_id):
            case is_null($this->design_id):
            case is_null($this->customizer):
            // roster
            // application sizes
                return static::STATUS_INCOMPLETE;

            case $this->is_approved && !$this->has_change_request && !$this->has_pending_approval:
                return static::STATUS_APPROVED;

            case !$this->is_approved && $this->has_change_request && !$this->has_pending_approval:
                return static::STATUS_REVIEW_CHANGES;

            case !$this->is_approved && !$this->has_change_request && $this->has_pending_approval:
                return static::STATUS_PENDING_APPROVAL;

            case !$this->is_approved && !$this->has_change_request && !$this->has_pending_approval:
                return static::STATUS_GET_APPROVAL;
        }

        return null;
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
