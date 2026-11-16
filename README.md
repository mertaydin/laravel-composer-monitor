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
    'user_ids' => [1, 2],
    'user_model' => App\\Models\\User::class,
    'dedupe_ttl' => 86400,
],
```

The threshold rule fires when the number of outdated dependencies reaches the configured value. Package rules alert when a watched package appears in Composer's outdated report. Alert fingerprints are cached to avoid sending the same update repeatedly; `dedupe_ttl` controls the suppression window in seconds.

- Email uses Laravel's configured mail transport. Set `mail_to` to one or more recipient addresses.
- Database notifications use Laravel's Notification system. Set `user_ids` and optionally `user_model` (which defaults to the configured auth user model). The model must use Laravel's `Notifiable` trait, and the application must have the notifications table.
- Slack and Discord use their respective incoming webhook URLs.
- Dashboard alerts are retained in the latest 50 alert history entries.

Set `COMPOSER_MONITOR_ALERTS_ENABLED=true`, `COMPOSER_MONITOR_ALERT_OUTDATED_THRESHOLD=5`, `COMPOSER_MONITOR_ALERT_MAIL_TO=ops@example.com`, `COMPOSER_MONITOR_SLACK_WEBHOOK` and `COMPOSER_MONITOR_DISCORD_WEBHOOK` as needed. Watched package names and channel recipients are configured in the published PHP config.

## Routes and access control

The default dashboard route is `/composer-monitor`. Restrict it in the published config:

```php
'route' => [
    'middleware' => ['web', 'auth'],
],
```

## License

MIT
