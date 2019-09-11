<?php

namespace App\Models;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Model;

class ClientInformation extends Model
{
    protected $fillable = ["school_name", "first_name", "last_name", "email", "business_phone", "address_1", "address_2", "city", "state", "zip_code", "approval_token"];

    public function cart_item()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function scopeFindBy($query, $field, $value)
    {
        return $query->where($field, $value);
    }

    public static function generateUniqueApprovalToken()
    {
        $approval_tokens = static::all()->pluck('approval_token')->toArray();

        do {
            $new_token = sha1(uniqid());
        } while (in_array($new_token, $approval_tokens));

        return $new_token;
    }
}
