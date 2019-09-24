<?php

namespace App\Models;

use App\Api\Prolook\MaterialApi;
use App\Api\Prolook\SavedDesignApi;
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

    protected $hidden = ["pl_cart_id"];

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
        $items = $this->cart_items;

        $rows = [];
        if ($items->isNotEmpty())
        {
            foreach ($items as $item)
            {
                $rows[] = [
                    'line_id' => $item->line_item_id,
                    'cut_id' => $item->cut_id,
                    'cut_name' => "",
                    'style_id' => $item->style_id,
                    'style_name' => "",
                    'design_id' => $item->design_id,
                    'design_status' => $item->getStatus(),
                    'customizer_url' => $item->getCustomizerUrl(),
                    'roster' => $item->roster,
                    'active' => !$item->trashed() ? 1 : 0
                ];
            }
        }

        return $rows;
    }

    public function getCartItemsByOrderFormat()
    {
        $items = $this->cart_items;

        $data = [];

        // garbage data
        $data['order'] = [
            'client' => "",
            'submitted' => "",
            'sku' => "",
            'material_id' => "",
            'url' => "",
            'user_name' => "",
            'po_number' => "",
            'magento_order_number' => "",
            'brand' => "",
            'test_order' => ""
        ];
        $data['athletic_director'] = [
            'contact' => "",
            'email' => "",
            'phone' => "",
            'fax' => ""
        ];
        $data['billing'] = [
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

        $materialApi = new MaterialApi;
        $savedDesignApi = new SavedDesignApi;
        $brand = config("app.brand");

        $orderItems = [];
        foreach ($items as $index => $item) {
            if (!is_null($item->client_information))
            {
                $clientInfo = $item->client_information;

                $shipping_info = [
                    'school_name' => $clientInfo->school_name,
                    'first_name' => $clientInfo->first_name,
                    'last_name' => $clientInfo->last_name,
                    'email' => $clientInfo->email,
                    'business_phone' => $clientInfo->business_phone,
                    'address_1' => $clientInfo->address_1,
                    'address_2' => $clientInfo->address_2,
                    'city' => $clientInfo->city,
                    'state' => $clientInfo->state,
                    "zip_code" => $clientInfo->zip_code
                ];

                $orderItems[$index]['shipping_info'] = $shipping_info;
            }

            $orderItems[$index]['brand'] = $brand;
            $orderItems[$index]['item_id'] = $item->line_item_id;
            $orderItems[$index]['type'] = "";
            $orderItems[$index]['description'] = "";
            $orderItems[$index]['builder_customization'] = "";
            $orderItems[$index]['set_group_id'] = 0;
            $orderItems[$index]['factory_order_id'] = "";
            $orderItems[$index]['design_sheet'] = "";

            $orderItems[$index]['roster'] = json_decode($item->roster);
            $orderItems[$index]['sku'] = "";
            $orderItems[$index]['material_id'] = $item->style_id;
            $orderItems[$index]['url'] = $item->getCustomizerUrl();
            $orderItems[$index]['price'] = "Call for Pricing";
            $orderItems[$index]['applicationType'] = "";
            $orderItems[$index]['application_type'] = "";
            $orderItems[$index]['additional_attachments'] = "";
            $orderItems[$index]['notes'] = "";

            $materialResult = $materialApi->getById($item->style_id);
            if ($materialResult->success)
            {
                $material = $materialResult->material;

                $orderItems[$index]['type'] = $material->type;
                $orderItems[$index]['description'] = $material->description;
                $orderItems[$index]['applicationType'] = ucwords(str_replace("_", " ", $material->uniform_application_type), " ");
                $orderItems[$index]['application_type'] = $material->uniform_application_type;
            }

            $savedDesignResult = $materialApi->getById($item->design_id);
            if ($savedDesignResult->success)
            {
                $savedDesign = $savedDesignResult->saved_design;

                $orderItems[$index]['builder_customization'] = $$savedDesign->builder_customizations;
            }
        }

        $data['order_items'] = $orderItems;

        return $data;
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
