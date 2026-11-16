# Laravel Composer Monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mertaydin/laravel-composer-monitor.svg?style=flat-square)](https://packagist.org/packages/mertaydin/laravel-composer-monitor)
[![License](https://img.shields.io/github/license/mertaydin/laravel-composer-monitor?style=flat-square)](LICENSE)

A lightweight dashboard for monitoring Composer dependencies, available updates, security advisories and abandoned packages in Laravel applications.

## Requirements

- PHP 8.2+
- Laravel 10, 11, 12 or 13
- Composer 2

## Installation

```bash
composer require mertaydin/laravel-composer-monitor
```

Publish the configuration when you need to customize the routes or runtime:

```bash
php artisan vendor:publish --tag=composer-monitor-config
```

Open `/composer-monitor` to view the dashboard. The default route middleware is `web`; add your application's authentication middleware before exposing it in production.

## Configuration

| Key | Default | Description |
| --- | --- | --- |
| `route.enabled` | `true` | Register the dashboard routes |
| `route.prefix` | `composer-monitor` | URL prefix |
| `route.name` | `composer-monitor.` | Route name prefix |
| `route.middleware` | `['web']` | Route middleware |
| `layout` | `null` | Optional Blade layout to extend |
| `composer_binary` | `composer` | Composer executable |
| `timeout` | `120` | Command timeout in seconds |
| `blocker_limit` | `30` | Maximum outdated packages inspected for dependency blockers |
| `cache.key` | `composer-monitor:latest` | Cache key |
| `cache.ttl` | `null` | Cache lifetime; null keeps the latest result |

## Usage

Run a check from the dashboard or the command line:

```bash
php artisan composer-monitor:check
```

The monitor reads `composer.json` and `composer.lock`, then runs Composer's locked audit and outdated checks. It does not install, update or modify dependencies.

## Access control

The dashboard is intended for trusted administrators. Configure middleware in `config/composer-monitor.php`, for example:

```php
'route' => [
    'middleware' => ['web', 'auth'],
],
```

## License

MIT. See [LICENSE](LICENSE).
