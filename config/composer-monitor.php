<?php

return [
    'route' => [
        'enabled' => true,
        'prefix' => 'composer-monitor',
        'name' => 'composer-monitor.',
        'middleware' => ['web'],
    ],

    'layout' => null,

    'composer_binary' => env('COMPOSER_BINARY', 'composer'),
    'timeout' => 120,
    'blocker_limit' => 30,

    'cache' => [
        'store' => env('COMPOSER_MONITOR_CACHE_STORE'),
        'key' => 'composer-monitor:latest',
        'ttl' => null,
    ],

    'composer_home' => storage_path('app/composer-monitor'),
];
