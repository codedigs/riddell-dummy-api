<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MultipleUrl implements Rule
{
    public function passes($attribute, $value)
    {
        $urls = explode(",", $value);

        $result = array_map(function($url) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }, $urls);

        $unique_result = array_unique($result);

        return count($unique_result) === 1 && $unique_result[0] === true;
    }

    public function message()
    {
        return "The :attribute must be valid urls";
    }
}
