<?php

namespace App\Http\Controllers;

use App\Models\ZipCode;

class ZipCodeController extends Controller
{
    public function getStates()
    {
        $states = ZipCode::getAllStates();

        return response()->json([
            'success' => true,
            'states' => $states
        ]);
    }

    public function getCitiesByStateCode($state_code)
    {
        $cities = ZipCode::getCitiesByStateCode($state_code);

        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }

    public function getZipCodesByStateCodeAndCity($state_code, $city)
    {
        $state_code = urldecode($state_code);
        $city = urldecode($city);

        $zip_codes = ZipCode::getZipCodesByStateCodeAndCity($state_code, $city);

        return response()->json([
            'success' => true,
            'zip_codes' => $zip_codes
        ]);
    }
}
