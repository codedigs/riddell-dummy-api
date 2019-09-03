<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientInformation extends Model
{
    protected $fillable = ["school_name", "first_name", "last_name", "email", "business_phone", "address_1", "address_2", "city", "state", "zip_code"];
}
