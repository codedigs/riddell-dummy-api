<?php

return [
    'name' => env("APP_NAME"),
    'key' => env("APP_KEY"),
    'locale' => env("APP_LOCALE", "en"),
    'brand' => env("BRAND", "Riddell"),

    'timezone' => env("TIMEZONE"),

    'use_cuts_in_db' => env("USE_CUTS_IN_DB", false),

    'under_maintenance' => filter_var(env("UNDER_MAINTENANCE", false), FILTER_VALIDATE_BOOLEAN)
];
