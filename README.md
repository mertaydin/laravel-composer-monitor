# Laravel Composer Monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mertaydin/laravel-composer-monitor.svg?style=flat-square)](https://packagist.org/packages/mertaydin/laravel-composer-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/mertaydin/laravel-composer-monitor.svg?style=flat-square)](https://packagist.org/packages/mertaydin/laravel-composer-monitor)
[![License](https://img.shields.io/packagist/l/mertaydin/laravel-composer-monitor.svg?style=flat-square)](LICENSE)
[![Tests](https://img.shields.io/github/actions/workflow/status/mertaydin/laravel-composer-monitor/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mertaydin/laravel-composer-monitor/actions)

A lightweight Laravel dashboard for monitoring Composer dependency health: installed packages, available updates, security advisories, abandoned dependencies and update blockers.

## Features

- Dependency inventory with direct/transitive and production/development classification.
- Outdated packages, security advisories, abandoned dependencies and update blockers.
- Turkish and English interface language selector.
- Light, dark and system appearance modes.
- Configurable alert rules: outdated-package count threshold and selected package updates.
- Alert delivery to dashboard history, email, Laravel database notifications, Slack and Discord.
- Artisan command and dashboard-triggered checks. Checks never install or modify dependencies.

## Requirements

- PHP 8.2+
- Laravel 10, 11, 12 or 13
- Composer 2

## Installation

```bash
composer require mertaydin/laravel-composer-monitor
php artisan vendor:publish --tag=composer-monitor-config
```

Dashboard: `/composer-monitor`

Run a check:

```bash
php artisan composer-monitor:check
```

## Alert configuration

Configure `config/composer-monitor.php`:

```php
'alerts' => [
    'enabled' => true,
    'outdated_threshold' => 5,
    'packages' => [
        'laravel/framework',
        'guzzlehttp/guzzle',
    ],
    'channels' => ['dashboard', 'mail', 'database', 'slack', 'discord'],
    'mail_to' => ['ops@example.com'],
    'slack_webhook' => env('COMPOSER_MONITOR_SLACK_WEBHOOK'),
    'discord_webhook' => env('COMPOSER_MONITOR_DISCORD_WEBHOOK'),
    // Notifiable application users for Laravel's database notification channel.
    'users' => [],
],
```

The threshold rule fires when the number of outdated dependencies reaches the configured value. Package rules alert when a watched package appears in Composer's outdated report. Alert fingerprints are cached to prevent the same update from generating a notification on every check.

Set `COMPOSER_MONITOR_ALERTS_ENABLED=true`, `COMPOSER_MONITOR_ALERT_OUTDATED_THRESHOLD=5`, `COMPOSER_MONITOR_ALERT_MAIL_TO=ops@example.com`, `COMPOSER_MONITOR_SLACK_WEBHOOK` and `COMPOSER_MONITOR_DISCORD_WEBHOOK` in the application environment as needed.

Email uses Laravel's configured mail transport. Database delivery uses Laravel Notifications and requires the application's notifications table. Supply notifiable user instances in the `alerts.users` configuration at runtime when database delivery is enabled.

## Routes and access control

The default dashboard route is `/composer-monitor`. Restrict it in the published config:

```php
'route' => [
    'middleware' => ['web', 'auth'],
],
```

## License

MIT
