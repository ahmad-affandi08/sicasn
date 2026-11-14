<?php

return [

    'default' => 'log',

    'mailers' => [

        'log' => [
            'transport' => 'log',
            'channel' => null,
        ],

        'array' => [
            'transport' => 'array',
        ],

    ],

    'from' => [
        'address' => 'noreply@localhost',
        'name' => 'SICASN',
    ],

];
