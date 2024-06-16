<?php

return [
    // Uses either the API routes or the web routes
    // If you want to use the API routes, you need to set this to true
    // It's a bit of an experimental feature, since the API routes can be used in all kinds of ways. 
    'api' => false,

    // Caching in seconds
    'cache' => [
        'users_profiles' => 60,
        'emoji_all' => 120,
    ],

    'admin_password' => env('JACE_ADMIN_PASSWORD'),
];
