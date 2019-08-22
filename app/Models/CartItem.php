<?php

namespace App\Models;

use App\Api\Clients\CutApi;
use App\Models\Cart;
use App\Models\Cut;
use App\Models\Style;
use Illuminate\Database\Eloquent\Model;
use Log;

class CartItem extends Model
{
    protected $fillable = ["cut_id", "style_id", "design_id", "is_approved", "has_change_request", "has_pending_approval", "cart_id"];

    const STATUS_REVIEW_CHANGES = "review changes";
    const STATUS_APPROVED = "approved";
    const STATUS_PENDING_APPROVAL = "pending approval";
    const STATUS_GET_APPROVAL = "get approval";
    const STATUS_INCOMPLETE = "incomplete";

    const TRUTHY_FLAG = 1;
    const FALSY_FLAG = 0;

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function coach_request_logs()
    {
        return $this->hasMany(CoachRequestLog::class);
    }

    public function getCut()
    {
        // return Cut::find($this->cut_id);
        $cutApi = new CutApi;
        $result = $cutApi->getById(16);

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

    public function getStyle()
    {
        return Style::find($this->style_id);
    }

    public function getDesignId()
    {
        return $this->design_id;
    }

    public function getStatus()
    {
        switch(true) {
            case is_null($this->cut_id):
            case is_null($this->style_id):
            case is_null($this->design_id):
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

    public function markAsCoachHasChangeRequest()
    {
        $this->is_approved = static::FALSY_FLAG;
        $this->has_change_request = static::TRUTHY_FLAG;
        $this->has_pending_approval = static::FALSY_FLAG;

        return $this->save();
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
