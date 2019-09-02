<?php

namespace App\Models;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class CartItem extends Model
{
    use SoftDeletes;

    protected $fillable = ["cut_id", "style_id", "design_id", "is_approved", "has_change_request", "has_pending_approval", "line_item_id", "pl_cart_id_fk"];

    const STATUS_REVIEW_CHANGES = "review changes";
    const STATUS_APPROVED = "approved";
    const STATUS_PENDING_APPROVAL = "pending approval";
    const STATUS_GET_APPROVAL = "get approval";
    const STATUS_INCOMPLETE = "incomplete";

    const DESIGN_STATUS_INCOMPLETE = "incomplete";
    const DESIGN_STATUS_CONFIG_ERROR = "configuration error";
    const DESIGN_STATUS_COMPLETE = "complete";

    const TRUTHY_FLAG = 1;
    const FALSY_FLAG = 0;

    public function cart()
    {
        return $this->belongsTo(Cart::class, "pl_cart_id", "pl_cart_id_fk");
    }

    public function coach_request_logs()
    {
        return $this->hasMany(CoachRequestLog::class);
    }

    public function scopeFindBy($query, $field, $value)
    {
        return $query->where($field, $value);
    }

    public function getCutId()
    {
        return $this->cut_id;
    }

    public function getStyleId()
    {
        return $this->style_id;
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
            case $this->roster === "{}":
            case $this->application_size === "{}":
            case $this->designStatusIncomplete():
            case $this->designStatusConfigError():
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

    public function designStatusIncomplete()
    {
        return $this->design_status === static::DESIGN_STATUS_INCOMPLETE;
    }

    public function designStatusConfigError()
    {
        return $this->design_status === static::DESIGN_STATUS_CONFIG_ERROR;
    }

    public function markAsCoachHasChangeRequest()
    {
        $this->is_approved = static::FALSY_FLAG;
        $this->has_change_request = static::TRUTHY_FLAG;
        $this->has_pending_approval = static::FALSY_FLAG;

        return $this->save();
    }

    public function approved()
    {
        $this->is_approved = static::TRUTHY_FLAG;
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
