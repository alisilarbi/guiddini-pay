<?php

return [
    'env' => env('PAYMENT_ENV', 'development'),
    'credentials' => [
        'production' => [
            'username' => env('SATIM_PRODUCTION_USERNAME'),
            'password' => env('SATIM_PRODUCTION_PASSWORD'),
            'terminal' => env('SATIM_PRODUCTION_TERMINAL'),
        ],
        'development' => [
            'username' => env('SATIM_DEVELOPMENT_USERNAME'),
            'password' => env('SATIM_DEVELOPMENT_PASSWORD'),
            'terminal' => env('SATIM_DEVELOPMENT_TERMINAL'),
        ],
    ],
];