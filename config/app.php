<?php

return [
    'key' => env("APP_KEY"),
    'locale' => env("APP_LOCALE", "en"),

    'under_maintenance' => filter_var(env("UNDER_MAINTENANCE", false), FILTER_VALIDATE_BOOLEAN)
];
