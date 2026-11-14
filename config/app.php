<?php

return [

    'name' => 'SICASN',

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => 'http://localhost',

    'timezone' => 'Asia/Jakarta',

    'locale' => 'id',

    'fallback_locale' => 'id',

    'faker_locale' => 'id_ID',

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [],

    'maintenance' => [
        'driver' => 'file',
        'store' => null,
    ],

];
