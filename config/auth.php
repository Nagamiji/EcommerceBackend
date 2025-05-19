<?php
// config/auth.php

return [

    'defaults' => [
        'guard'   => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Use Sanctum for “api”
        'api' => [
            'driver'   => 'sanctum',
            'provider' => 'users',
            // 'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],
    ],

    'password_timeout' => 10800,
    // … passwords, timeouts, etc …
];


    
