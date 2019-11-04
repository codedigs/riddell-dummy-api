<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    protected $fillable = ['zip_code', 'state_code', 'state', 'city'];

    public static function getAllStates()
    {
        return static::all()
                    ->unique("state")
                    ->pluck("state", "state_code");
    }

    public static function getCitiesByStateCode($state_code)
    {
        return static::where('state_code', $state_code)
                    ->get()
                    ->unique("city")
                    ->pluck("city");
    }

    public static function getZipCodesByStateCodeAndCity($state_code, $city)
    {
        \Log::debug(print_r([$state_code, $city], true));

        return static::where('state_code', $state_code)
                    ->where('city', $city)
                    ->get()
                    ->pluck("zip_code");
    }
}
