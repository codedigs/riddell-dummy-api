<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\ClientInformation;
use App\Models\Cut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class CartItem extends Model
{
    use SoftDeletes;

    protected $fillable = ["cut_id", "style_id", "design_id", "is_approved", "has_change_request", "has_pending_approval", "line_item_id", "pl_cart_id_fk"];

    protected $hidden = ["pl_cart_id_fk", "line_item_id"];

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

    const NO_IMAGE_PLACEHOLDER = "https://via.placeholder.com/1000x1100?text=No%20Image";

    public function cart()
    {
        return $this->belongsTo(Cart::class, "pl_cart_id_fk", "pl_cart_id");
    }

    public function changes_logs()
    {
        return $this->hasMany(ChangeLog::class);
    }

    public function client_information()
    {
        return $this->hasOne(ClientInformation::class);
    }

    public function cut()
    {
        return $this->hasOne(Cut::class, "cut_id", "cut_id");
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

    public function getCustomizerUrl()
    {
        $material_id = $this->style_id;

        if (is_null($this->design_id) || $this->design_id === 0) // stock builder
        {
            return "/riddell/customize/{$material_id}?cart_item_id=" . $this->id;
            // return "/builder/0/{$material_id}?cart_item_id=" . $this->id;
        }
        else // design id
        {
            $design_id = $this->design_id;
            return "/my-saved-design/{$design_id}/render?cart_item_id=" . $this->id;
        }
    }

    public function getStatus()
    {
        switch(true) {
            case is_null($this->cut_id) || $this->cut_id === 0:
            case is_null($this->style_id) || $this->style_id === 0:
            case is_null($this->design_id) || $this->design_id === 0:
            case $this->isRosterEmpty():
            case $this->isAppSizeEmpty():

            //  temporary comment these below
            // case $this->designStatusIncomplete():
            // case $this->designStatusConfigError():
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

    public function isRosterEmpty()
    {
        return is_null($this->roster) || $this->roster === "[]" || $this->roster === "{}";
    }

    public function isAppSizeEmpty()
    {
        return is_null($this->application_size) || $this->application_size === "[]" || $this->application_size === "{}";
    }

    public function isIncomplete()
    {
        return $this->getStatus() === static::STATUS_INCOMPLETE;
    }

    public function isApproved()
    {
        return $this->getStatus() === static::STATUS_APPROVED;
    }

    public function isReviewChanges()
    {
        return $this->getStatus() === static::STATUS_REVIEW_CHANGES;
    }

    public function isPendingApproval()
    {
        return $this->getStatus() === static::STATUS_PENDING_APPROVAL;
    }

    public function isGetApproval()
    {
        return $this->getStatus() === static::STATUS_GET_APPROVAL;
    }

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

    public function designStatusIncomplete()
    {
        return $this->design_status === static::DESIGN_STATUS_INCOMPLETE;
    }

    public function designStatusConfigError()
    {
        return $this->design_status === static::DESIGN_STATUS_CONFIG_ERROR;
    }

    public function markAsHasChangeRequest()
    {
        $this->is_approved = static::FALSY_FLAG;
        $this->has_change_request = static::TRUTHY_FLAG;
        $this->has_pending_approval = static::FALSY_FLAG;

        return $this->save();
    }

    public function markAsPendingApproval()
    {
        $this->is_approved = static::FALSY_FLAG;
        $this->has_change_request = static::FALSY_FLAG;
        $this->has_pending_approval = static::TRUTHY_FLAG;

        return $this->save();
    }

    public function markAsIncomplete($remove_coach_link=true)
    {
        if ($remove_coach_link)
        {
            if (!is_null($this->client_information))
            {
                $this->client_information->approval_token = null;
                $this->client_information->save();
            }
        }

        $this->is_approved = static::FALSY_FLAG;
        $this->has_change_request = static::FALSY_FLAG;
        $this->has_pending_approval = static::FALSY_FLAG;

        return $this->save();
    }

    public function markAsApproved()
    {
        $this->is_approved = static::TRUTHY_FLAG;
        $this->has_change_request = static::FALSY_FLAG;
        $this->has_pending_approval = static::FALSY_FLAG;
        $this->approved_at = date("Y-m-d h:i:s");

        return $this->save();
    }

    public function getRosterSizeBreakDown()
    {
        if (!$this->isRosterEmpty())
        {
            $rosters = json_decode($this->roster, true);
            $sizeBreakdown = array_map(function($roster) {
                return [
                    'qty' => $roster['qty'],
                    'size' => $roster['size']
                ];
            }, $rosters);

            return $sizeBreakdown;
        }

        return null;
    }

    public function getRosterOrderFormat($cut_name)
    {
        if (!$this->isRosterEmpty())
        {
            $rosters = json_decode($this->roster, true);

            $rosterOrderFormat = [];
            foreach ($rosters as $roster)
            {
                if (count($roster['rosters']) > 0)
                {
                    $subRosters = $roster['rosters'];

                    foreach ($subRosters as $subRoster)
                    {
                        $rosterOrderFormat[] = [
                            'Size' => $subRoster['size'],
                            'Number' => $subRoster['number'],
                            'Name' => $subRoster['player_name'],
                            'Sample' => 0,
                            'LastNameApplication' => "N/A",
                            'SleeveCut' => $cut_name,
                            'Quantity' => $subRoster['qty']
                        ];
                    }
                }
                else
                {
                    $rosterOrderFormat[] = [
                        'Size' => $roster['size'],
                        'Number' => "",
                        'Name' => "",
                        'Sample' => 0,
                        'LastNameApplication' => "N/A",
                        'SleeveCut' => $cut_name,
                        'Quantity' => $roster['qty']
                    ];
                }
            }

            return $rosterOrderFormat;
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
