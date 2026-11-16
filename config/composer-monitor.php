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
    'alerts' => [
        'enabled' => env('COMPOSER_MONITOR_ALERTS_ENABLED', false),
        'outdated_threshold' => (int) env('COMPOSER_MONITOR_ALERT_OUTDATED_THRESHOLD', 0),
        'packages' => [],
        'channels' => ['dashboard', 'mail', 'database', 'slack', 'discord'],
        'mail_to' => array_filter(array_map('trim', explode(',', (string) env('COMPOSER_MONITOR_ALERT_MAIL_TO', '')))),
        'users' => [],
        'slack_webhook' => env('COMPOSER_MONITOR_SLACK_WEBHOOK'),
        'discord_webhook' => env('COMPOSER_MONITOR_DISCORD_WEBHOOK'),
        'history_key' => 'composer-monitor:alerts',
    ],
    'composer_home' => storage_path('app/composer-monitor'),
];
