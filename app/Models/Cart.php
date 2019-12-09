<?php

namespace App\Models;

use App\Api\Prolook\MaterialApi;
use App\Api\Prolook\SavedDesignApi;
use App\Api\Qx7\CutApi;
use App\Models\CartItem;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    // const LIFE_SPAN = 60 * 60; // 1 hour
    const CART_TOKEN_PREFIX = "cart_token_";
    // const SAVE_ORDER_ACTION = "save_order";
    // const SUBMITTED_FLAG = 1;
    const TRUTHY_FLAG = 1;

    protected $fillable = ["pl_cart_id", "is_active", "is_completed", "is_abandoned", "user_id"];

    public function cart_items()
    {
        return $this->hasMany(CartItem::class, "pl_cart_id_fk", "pl_cart_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFindBy($query, $field, $value)
    {
        return $query->where($field, $value);
    }

    /**
     * Valid cart basis
     *
     * @param  Illuminate\Database\Query\Builder $query
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeValidToUse($query)
    {
        return $query->where('is_active', 1)
                    ->where('is_completed', 0)
                    ->where('is_abandoned', 0);
    }

    public function isCompleted()
    {
        return $this->is_completed == 1;
    }

    // public function hasOwner()
    // {
    //     return !is_null($this->user);
    // }

    public function assignToUser($user_id)
    {
        $this->user_id = $user_id;
        return $this->save();
    }

    // public function mergeFromValidCarts($valid_carts)
    // {
    //     foreach ($valid_carts as $cart)
    //     {
    //         if ($cart->id !== $this->id)
    //         {
    //             if (!$cart->cart_items->isEmpty())
    //             {
    //                 $cart->cart_items()->update(['cart_id' => $this->id]);
    //             }

    //             // delete old cart
    //             $cart->delete();
    //         }
    //     }
    // }

    public function getCartItemsByHybrisFormat()
    {
        $items = $this->cart_items()
                    ->withTrashed()
                    ->get();

        $rows = [];
        if ($items->isNotEmpty())
        {
            $cutApi = new CutApi;
            $materialApi = new MaterialApi;

            foreach ($items as $item)
            {
                $cut_name = "";
                $style_name = "";

                if (!empty($item->cut_id))
                {
                    $cutResult = $cutApi->getById($item->cut_id);

                    if ($cutResult->success)
                    {
                        if (isset($cutResult->master_3d_block_patterns))
                        {
                            if (isset($cutResult->master_3d_block_patterns->block_pattern_name))
                            {
                                $cut_name = $cutResult->master_3d_block_patterns->block_pattern_name;
                            }
                        }
                    }
                }

                if (!empty($item->style_id))
                {
                    $materialResult = $materialApi->getById($item->style_id);
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

                $rows[] = [
                    // 'cutID' => $item->cut_id,
                    // 'cutName' => "Cut Name ". $item->cut_id ."(Dummy)",
                    // 'customizerStyleID' => $item->style_id,
                    // 'customizerStyleName' => "Customizer Style Name ".$item->style_id." (Dummy)",
                    // 'designID' => $item->design_id,
                    // 'designStatus' => $item->getStatus(),
                    // 'lineItemID' => $item->line_item_id,
                    // 'styleDescription' => "Style Description (Dummy)",
                    // 'url' => $item->getCustomizerUrl(),
                    // 'variants' => $item->roster,

                    'line_id' => $item->line_item_id,
                    'cut_id' => $item->cut_id,
                    'cut_name' => $cut_name,
                    'style_id' => $item->style_id,
                    'style_name' => $style_name,
                    'design_id' => $item->design_id,
                    'design_status' => $item->getStatus(),
                    'customizer_url' => $item->getCustomizerUrl(),
                    'roster' => $item->roster,
                    'active' => $item->trashed() ? 0 : 1
                ];
            }
        }

        return $rows;
    }

    public function getCartItemsByOrderFormat()
    {
        $SELECTED_SOURCE = "Riddell Customizer";
        $SELECTED_TEMPLATE = "Riddell";

        $items = $this->cart_items;
        $user = $this->user;
        $brand = config("app.brand");

        $data = [];

        $data['action'] = "submit_order";

        $data['order'] = [
            'brand' => $brand,
            'user_id' => $user->user_id,
            'user_name' => $user->email,
            'pl_cart_id' => $this->pl_cart_id
        ];

        $data['shipping'] = [
            'organization' => "",
            'contact' => "",
            'email' => "",
            'address' => "",
            'city' => "",
            'state' => "",
            'phone' => "",
            'fax' => "",
            'zip' => ""
        ];
        if (!is_null($user))
        {
            if (!is_null($shippingInfo = $user->shipping_information))
            {
                $data['shipping']['organization'] = $shippingInfo->school_name;
                $data['shipping']['contact'] = $shippingInfo->fullname();
                $data['shipping']['email'] = $shippingInfo->email;
                $data['shipping']['address'] = $shippingInfo->address_1;
                $data['shipping']['city'] = $shippingInfo->city;
                $data['shipping']['state'] = $shippingInfo->state;
                $data['shipping']['phone'] = $shippingInfo->business_phone;
                $data['shipping']['fax'] = ""; // blank for the mean time
                $data['shipping']['zip'] = $shippingInfo->zip;
            }
        }

        $materialApi = new MaterialApi;
        $savedDesignApi = new SavedDesignApi;
        $cutApi = new CutApi;

        $brand = config("app.brand");

        $orderItems = [];
        foreach ($items as $index => $item) {
            if (!is_null($item->client_information))
            {
                $clientInfo = $item->client_information;

                $shipping_info = [
                    'organization' => $clientInfo->school_name,
                    'contact' => $clientInfo->fullname(),
                    'email' => $clientInfo->email,
                    'address' => $clientInfo->address_1,
                    'city' => $clientInfo->city,
                    'state' => "",
                    'phone' => $clientInfo->business_phone,
                    'fax' => "", // blank for the mean time
                    // 'address_2' => $clientInfo->address_2,
                    "zip" => $clientInfo->zip_code
                ];

                $orderItems[$index]['shipping'] = $shipping_info;
            }

            $cut_name = "";
            $style_name = "";

            if (!empty($item->cut_id))
            {
                $cutResult = $cutApi->getById($item->cut_id);

                if ($cutResult->success)
                {
                    if (isset($cutResult->master_3d_block_patterns))
                    {
                        if (isset($cutResult->master_3d_block_patterns->block_pattern_name))
                        {
                            $cut_name = $cutResult->master_3d_block_patterns->block_pattern_name;
                        }
                    }
                }
            }

            if (!empty($item->style_id))
            {
                $materialResult = $materialApi->getById($item->style_id);
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

            $builder_customization = json_decode($item->builder_customization, true);
            $item_id = "";
            if (isset($builder_customization['item_id']))
            {
                $item_id = $builder_customization['item_id'];
            }

            $orderItems[$index]['brand'] = $brand;
            $orderItems[$index]['line_item_id'] = $item->line_item_id;
            $orderItems[$index]['item_id'] = $item_id;
            $orderItems[$index]['customizer_style_id'] = $item->style_id;
            $orderItems[$index]['builder_customizations'] = $item->builder_customization;
            $orderItems[$index]['type'] = ""; // meron
            $orderItems[$index]['description'] = ""; // meron
            $orderItems[$index]['factory_order_id'] = "";

            $orderItems[$index]['roster'] = $item->getRosterOrderFormat($cut_name);
            $orderItems[$index]['sku'] = "";
            $orderItems[$index]['material_id'] = $item->style_id;
            $orderItems[$index]['url'] = $item->getCustomizerUrl();
            $orderItems[$index]['application_type'] = "";

            if (!is_null($item->cut))
            {
                $orderItems[$index]['sku'] = $item->cut->hybris_sku;
            }

            $materialResult = $materialApi->getById($item->style_id);
            if ($materialResult->success)
            {
                $material = $materialResult->material;

                $orderItems[$index]['type'] = $material->type;
                $orderItems[$index]['description'] = $material->description;
                $orderItems[$index]['applicationType'] = ucwords(str_replace("_", " ", $material->uniform_application_type), " ");
                $orderItems[$index]['application_type'] = $material->uniform_application_type;
            }

            // pdf json
            $orderItems[$index]['pdf_json'] = [
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
                'roster' => $item->getRosterOrderFormat($cut_name),
                'pipings' => [],
                'createdDate' => date("Y/m/d"),
                'notes' => "",
                'sizeBreakdown' => $item->getRosterSizeBreakDown(),
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
                    "pl_cart_id" => $item->pl_cart_id_fk,
                    "line_item_id" => $item->line_item_id,
                    "cut_id" => $item->cut_id,
                    "cut_name" => $cut_name,
                    "style_id" => $item->style_id,
                    "style_name" => $style_name,
                    "design_id" => $item->design_id
                ],
                'colorGroupings' => []
            ];

            if (isset($builder_customization['thumbnails']))
            {
                $orderItems[$index]['pdf_json']['thumbnails'] = $builder_customization['thumbnails'];
            }

            if (isset($builder_customization['uniform_category']))
            {
                $orderItems[$index]['pdf_json']['category'] = $builder_customization['uniform_category'];
            }

            if (isset($builder_customization['material']))
            {
                if (isset($builder_customization['material']['block_pattern']))
                {
                    $orderItems[$index]['pdf_json']['description'] = $builder_customization['material']['block_pattern'];
                }
            }

            if (isset($builder_customization['cut_pdf']))
            {
                $orderItems[$index]['pdf_json']['cutPdf'] = $builder_customization['cut_pdf'];
            }

            if (isset($builder_customization['styles_pdf']))
            {
                $orderItems[$index]['pdf_json']['stylesPdf'] = $builder_customization['styles_pdf'];
            }

            if (isset($builder_customization['pipings']))
            {
                $orderItems[$index]['pdf_json']['pipings'] = $builder_customization['pipings'];
            }

            if (isset($builder_customization['applications']))
            {
                $orderItems[$index]['pdf_json']['applications'] = $builder_customization['applications'];
            }

            if (isset($builder_customization['upper']))
            {
                $orderItems[$index]['pdf_json']['upper'] = $builder_customization['upper'];
            }

            if (isset($builder_customization['lower']))
            {
                $orderItems[$index]['pdf_json']['lower'] = $builder_customization['lower'];
            }

            if (isset($builder_customization['lower']))
            {
                $orderItems[$index]['pdf_json']['lower'] = $builder_customization['lower'];
            }

            if (isset($builder_customization['hiddenBody']))
            {
                $orderItems[$index]['pdf_json']['hiddenBody'] = $builder_customization['hiddenBody'];
            }

            if (isset($builder_customization['randomFeeds']))
            {
                $orderItems[$index]['pdf_json']['randomFeeds'] = $builder_customization['randomFeeds'];
            }

            if (isset($builder_customization['material']))
            {
                if (isset($builder_customization['material']['uniform_application_type']))
                {
                    $orderItems[$index]['pdf_json']['applicationType'] = $builder_customization['material']['uniform_application_type'];
                }

                if (isset($builder_customization['material']['modifier_labels']))
                {
                    $orderItems[$index]['pdf_json']['sml'] = $builder_customization['material']['modifier_labels'];
                }
            }

            if (isset($builder_customization['colorGroupings']))
            {
                $orderItems[$index]['pdf_json']['colorGroupings'] = $builder_customization['colorGroupings'];
            }
        }

        $data['order_items'] = $orderItems;
        $data['message'] = "success";
        $data['status'] = true;

        return $data;
    }

    public function markAsCompleted()
    {
        $this->is_completed = static::TRUTHY_FLAG;
        return $this->save();
    }

    public function areAllItemsApproved()
    {
        $result = $this->cart_items()
                    ->get()
                    ->pluck("is_approved")
                    ->toArray();

        if (count($result) > 0) {
            $result = array_unique($result);
            return $result[0] === 1 && count($result) === 1;
        }

        return false;
    }

    public static function findByProlookCartId($pl_cart_id)
    {
        return static::where('pl_cart_id', $pl_cart_id)
                ->validToUse()
                ->get()
                ->last();
    }

    /**
     * @param  User|null $user
     * @return Cart
     */
    // public static function takeCart()
    // {
    //     $unique_token = static::generateUniqueToken();

    //     $cart = static::create([
    //         'user_id' => null,
    //         'token' => $unique_token,
    //         'is_active' => static::TRUTHY_FLAG
    //     ]);

    //     return $cart;
    // }

    // public static function exceedInLifeSpan($timeout)
    // {
    //     $duration = time() - (int) $timeout;
    //     return $duration >= static::LIFE_SPAN;
    // }

    // public static function generateUniqueToken()
    // {
    //     $allCarts = static::all();

    //     if (!$allCarts->isEmpty())
    //     {
    //         $cart_tokens = $allCarts->pluck('token');

    //         do {
    //             $unique_token = uniqid(static::CART_TOKEN_PREFIX);
    //         } while (in_array($unique_token, $cart_tokens->toArray()));

    //         return $unique_token;
    //     }

    //     return uniqid(static::CART_TOKEN_PREFIX);
    // }

    // public static function abandon($cart_token)
    // {
    //     $cart = static::where('token', $cart_token)->get()->last();
    //     $cart->is_abandoned = 1;
    //     return $cart->save();
    // }
}
