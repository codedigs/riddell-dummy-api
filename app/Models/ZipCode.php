<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    protected $fillable = ['zip_code', 'state_code', 'state', 'city'];

    public static function getAllStates()
    {
        return static::all()
                    ->sortBy("state")
                    ->unique("state")
                    ->pluck("state", "state_code");
    }

    public static function getCitiesByStateCode($state_code)
    {
        return static::where('state_code', $state_code)
                    ->get()
                    ->filter(function($zipCode) {
                        return !empty($zipCode->city);
                    })
                    ->sortBy("city")
                    ->unique("city")
                    ->pluck("city");
    }

    public static function getZipCodesByStateCodeAndCity($state_code, $city)
    {
        return static::where('state_code', $state_code)
                    ->where('city', $city)
                    ->get()
                    ->filter(function($zipCode) {
                        return !empty($zipCode->zip_code);
                    })
                    ->sortBy("zip_code")
                    ->pluck("zip_code");
    }
}
