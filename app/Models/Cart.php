<?php

namespace App\Models;

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
