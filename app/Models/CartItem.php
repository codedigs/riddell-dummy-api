<?php

namespace App\Models;

use App\Api\Prolook\MaterialApi;
use App\Api\Qx7\GroupCutApi;
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

    const ROSTER_CATEGORY_ADULT = "adult";
    const ROSTER_CATEGORY_YOUTH = "youth";

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

    public function markAsApproved($timezone="America/New_York")
    {
        $this->is_approved = static::TRUTHY_FLAG;
        $this->has_change_request = static::FALSY_FLAG;
        $this->has_pending_approval = static::FALSY_FLAG;

        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone($timezone));
        $this->approved_at = $now->format("Y-m-d H:i:s");

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

    public function getPdfJson()
    {
        $SELECTED_SOURCE = "Riddell Customizer";
        $SELECTED_TEMPLATE = "Riddell";

        $cut_name = "";
        $style_name = "";

        $groupCutApi = new GroupCutApi;
        $materialApi = new MaterialApi;

        if (!empty($this->cut_id))
        {
            $groupCutResult = $groupCutApi->getById($this->cut_id);

            if ($groupCutResult->success)
            {
                if (isset($groupCutResult->master_block_pattern_group))
                {
                    if (isset($groupCutResult->master_block_pattern_group->name))
                    {
                        $cut_name = $groupCutResult->master_block_pattern_group->name;
                    }
                }
            }
        }

        if (!empty($this->style_id))
        {
            $materialResult = $materialApi->getById($this->style_id);
            if ($materialResult->success)
            {
                if (isset($materialResult->material))
                {
                    if (isset($materialResult->material->name))
                    {
                        $style_name = $materialResult->material->name;
                    }
                }
            }
        }

        $builder_customization = json_decode($this->builder_customization, true);

        $pdf_json = [
            'pdfGenerator' => true,
            'selectedSource' => $SELECTED_SOURCE,
            'selectedTemplate' => $SELECTED_TEMPLATE,
            'searchKey' => "preview" . date("-Y-m-d-H-i-s-") . uniqid(), // skip
            'thumbnails' => [
                'front_view' => "",
                'back_view' => "",
                'left_view' => "",
                'right_view' => ""
            ],
            'category' => "",
            'fullName' => "",
            'client' => "fullname",
            'orderId' => "",
            'foid' => "",
            'description' => "",
            'cutPdf' => "",
            'stylesPdf' => "",
            'roster' => $this->getRosterOrderFormat($cut_name),
            'pipings' => [],
            'createdDate' => date("Y/m/d"),
            'notes' => "",
            'sizeBreakdown' => $this->getRosterSizeBreakDown(),
            'applications' => [],
            'sizingTable' => [],
            'upper' => [],
            'lower' => [],
            'hiddenBody' => "",
            'randomFeeds' => [],
            'legacyPDF' => "",
            'applicationType' => "",
            'sml' => [],
            'sku' => "-",
            'hybris_cart_info' => [
                "hyb_cart_id" => "", // optional
                "pl_cart_id" => $this->pl_cart_id_fk,
                "line_item_id" => $this->line_item_id,
                "cut_id" => $this->cut_id,
                "cut_name" => $cut_name,
                "style_id" => $this->style_id,
                "style_name" => $style_name,
                "design_id" => $this->design_id
            ],
            'colorGroupings' => [],
            'signature' => $this->signature_image,
            'dateTimeStamp' => $this->approved_at
        ];

        if (isset($builder_customization['thumbnails']))
        {
            $pdf_json['thumbnails'] = $builder_customization['thumbnails'];
        }

        if (isset($builder_customization['uniform_category']))
        {
            $pdf_json['category'] = $builder_customization['uniform_category'];
        }

        if (isset($builder_customization['material']))
        {
            if (isset($builder_customization['material']['block_pattern']))
            {
                $pdf_json['description'] = $builder_customization['material']['block_pattern'];
            }
        }

        if (isset($builder_customization['cut_pdf']))
        {
            $pdf_json['cutPdf'] = $builder_customization['cut_pdf'];
        }

        if (isset($builder_customization['styles_pdf']))
        {
            $pdf_json['stylesPdf'] = $builder_customization['styles_pdf'];
        }

        if (isset($builder_customization['pipings']))
        {
            $pdf_json['pipings'] = $builder_customization['pipings'];
        }

        if (isset($builder_customization['applications']))
        {
            $pdf_json['applications'] = $builder_customization['applications'];
        }

        if (isset($builder_customization['upper']))
        {
            $pdf_json['upper'] = $builder_customization['upper'];
        }

        if (isset($builder_customization['lower']))
        {
            $pdf_json['lower'] = $builder_customization['lower'];
        }

        if (isset($builder_customization['lower']))
        {
            $pdf_json['lower'] = $builder_customization['lower'];
        }

        if (isset($builder_customization['hiddenBody']))
        {
            $pdf_json['hiddenBody'] = $builder_customization['hiddenBody'];
        }

        if (isset($builder_customization['randomFeeds']))
        {
            $pdf_json['randomFeeds'] = $builder_customization['randomFeeds'];
        }

        if (isset($builder_customization['material']))
        {
            if (isset($builder_customization['material']['uniform_application_type']))
            {
                $pdf_json['applicationType'] = $builder_customization['material']['uniform_application_type'];
            }

            if (isset($builder_customization['material']['modifier_labels']))
            {
                $pdf_json['sml'] = $builder_customization['material']['modifier_labels'];
            }
        }

        if (isset($builder_customization['colorGroupings']))
        {
            $pdf_json['colorGroupings'] = $builder_customization['colorGroupings'];
        }

        return $pdf_json;
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
                            'SleeveCut' => !empty($cut_name) ? $cut_name : "",
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
                        'SleeveCut' => !empty($cut_name) ? $cut_name : "",
                        'Quantity' => $roster['qty']
                    ];
                }
            }

            return $rosterOrderFormat;
        }

        return null;
    }

    public function saveLineItemId($line_item_id)
    {
        $this->line_item_id = $line_item_id;
        return $this->save();
    }

    public function deleteAdultRoster()
    {
        if (!$this->isRosterEmpty()) {
            $roster = json_decode($this->roster, true);

            $newRoster = array_filter($roster, function($r) {
                return $r['category'] === static::ROSTER_CATEGORY_YOUTH;
            });

            $this->roster = json_encode(array_values($newRoster));
            $saved = $this->save();

            if (empty($newRoster))
            {
                $this->delete();
            }

            return $saved;
        }

        $this->delete();
        return true;
    }

    public function deleteYouthRoster()
    {
        if (!$this->isRosterEmpty()) {
            $roster = json_decode($this->roster, true);

            $newRoster = array_filter($roster, function($r) {
                return $r['category'] === static::ROSTER_CATEGORY_ADULT;
            });

            $this->roster = json_encode(array_values($newRoster));
            $saved = $this->save();

            if (empty($newRoster))
            {
                $this->delete();
            }

            return $saved;
        }

        $this->delete();
        return true;
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
