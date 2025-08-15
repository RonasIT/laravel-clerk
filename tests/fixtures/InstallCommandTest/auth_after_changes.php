<?php

return [
    'defaults' => [
        'guard' => 'clerk',
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
        'clerk' => [
            'driver' => 'clerk_session',
            'provider' => 'users',
        ],
    ],
];
