<?php

return [
    'partner' => [
        'api' => [
            'url' => env('PARTNER_API_URL', 'https://api.foodlyapp.ge'),
            'email' => env('PARTNER_API_EMAIL'),
            'password' => env('PARTNER_API_PASSWORD'),
            'timeout' => env('PARTNER_API_TIMEOUT', 30),
        ],
        
        'token' => [
            'lifetime' => env('PARTNER_TOKEN_LIFETIME', 3600), // 1 hour
            'storage_path' => storage_path('app/tokens'),
        ]
    ]
];