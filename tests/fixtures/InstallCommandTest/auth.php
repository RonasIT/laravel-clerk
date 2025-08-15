<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
        'telescope' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],
];
