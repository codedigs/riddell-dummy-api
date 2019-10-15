<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingInformation extends Model
{
    protected $fillable = ["school_name", "first_name", "last_name", "email", "business_phone", "address_1", "address_2", "city", "state", "zip_code", "approval_token"];

    public static $rules = [
        'school_name' => "string|max:100",
        'first_name' => "required|string|max:50",
        'last_name' => "required|string|max:50",
        'email' => "required|string|max:50",
        'business_phone' => "string|max:20",
        'address_1' => "string|max:255",
        'address_2' => "string|max:255",
        'city' => "string|max:20",
        'state' => "string|max:20",
        'zip_code' => "numeric|digits_between:4,10"
    ];

    public function fullname()
    {
        return $this->first_name . " " . $this->last_name;
    }
}
