<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173', // Vue Server
        'http://127.0.0.1:5173',
        'http://localhost:3000',  // Alternativo
        env('FRONTEND_URL'), // Laravel env variable for frontend URL
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];