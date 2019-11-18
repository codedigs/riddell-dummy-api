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
        $items = $this->cart_items;
        $user = $this->user;
        $brand = config("app.brand");

        $data = [];

        $data['action'] = "submit_order";

        $data['order'] = [
            'brand' => $brand,
            'user_id' => $user->user_id,
            'user_name' => $user->email
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
                    'state' => $clientInfo->state,
                    'phone' => $clientInfo->business_phone,
                    'fax' => "", // blank for the mean time
                    // 'address_2' => $clientInfo->address_2,
                    "zip" => $clientInfo->zip_code
                ];

                $orderItems[$index]['shipping'] = $shipping_info;
            }

            $orderItems[$index]['brand'] = $brand;
            $orderItems[$index]['item_id'] = $item->line_item_id;
            $orderItems[$index]['customizer_style_id'] = $item->style_id;
            $orderItems[$index]['builder_customization'] = $item->builder_customization;
            $orderItems[$index]['type'] = ""; // meron
            $orderItems[$index]['description'] = ""; // meron
            $orderItems[$index]['factory_order_id'] = "";

            $orderItems[$index]['roster'] = json_decode($item->roster);
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
