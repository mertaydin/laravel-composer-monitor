# Laravel Composer Monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mertaydin/laravel-composer-monitor.svg?style=flat-square)](https://packagist.org/packages/mertaydin/laravel-composer-monitor)
[![Total Downloads](https://poser.pugx.org/mertaydin/laravel-composer-monitor/downloads)](https://packagist.org/packages/mertaydin/laravel-composer-monitor)
[![License](https://img.shields.io/packagist/l/mertaydin/laravel-composer-monitor.svg?style=flat-square)](LICENSE)
[![Tests](https://img.shields.io/github/actions/workflow/status/mertaydin/laravel-composer-monitor/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mertaydin/laravel-composer-monitor/actions)

A lightweight Laravel dashboard for monitoring Composer dependency health. Review installed packages, available updates, security advisories, abandoned dependencies and the constraints blocking updates — all from one place.

## Features

- **Dependency inventory:** browse installed production and development packages, versions and direct/transitive status.
- **Outdated packages:** identify dependencies with newer releases available.
- **Security monitoring:** review Composer security audit advisories and affected versions.
- **Abandoned packages:** identify packages marked as abandoned and suggested replacements.
- **Update blockers:** inspect dependency constraints that prevent an update.
- **Dashboard and Artisan command:** run checks from the web interface or CLI.
- **Six dashboard languages:** Turkish, English, Arabic, Spanish, Hindi and Simplified Chinese.
- **Appearance preferences:** light, dark and system themes.
- **Dependency alerts:** configurable update thresholds and watched packages, with dashboard, email, database notification, Slack and Discord delivery.
- **Dashboard access control:** authentication is required by default, with an optional callback for application-specific authorization.
- **Configurable runtime:** choose the Composer executable, timeout, cache and route middleware.
- **Laravel package discovery:** automatically registers its service provider.

## Requirements

- PHP 8.2 or newer
- Laravel 10, 11, 12 or 13
- Composer 2

## Installation

Install the package with Composer:

```bash
composer require mertaydin/laravel-composer-monitor
```

The dashboard is available at `/composer-monitor` by default. To place it under an existing application panel path, update `route.prefix` in `config/composer-monitor.php`:

```php
'route' => [
    'prefix' => 'panel/super/composer-monitor',
    'middleware' => ['web', 'auth', 'super-admin', 'super-ip'],
],
```

The dashboard will then be available at `/panel/super/composer-monitor`. Leading and trailing slashes are normalized automatically. Add your application's access-control middleware to `route.middleware` so it protects every package route:

Publish the configuration file to customize the package:

```bash
php artisan vendor:publish --tag=composer-monitor-config
```

## Usage

Run a dependency check from the dashboard or with Artisan:

```bash
php artisan composer-monitor:check
```

The monitor reads your application's `composer.json` and `composer.lock`, then runs Composer's locked audit and outdated checks. It does not install, update or modify your dependencies.

## Language and appearance

The dashboard includes six languages: Türkçe, English, العربية, Español, हिन्दी and 简体中文. Select a language from the dashboard header; the preference is stored in the current session. The appearance selector supports light, dark and system themes.

## Configuration

| Key | Default | Description |
| --- | --- | --- |
| `route.enabled` | `true` | Register the dashboard routes |
| `route.prefix` | `composer-monitor` | Dashboard URL prefix; supports nested paths such as `panel/super/composer-monitor` |
| `route.name` | `composer-monitor.` | Route name prefix |
| `route.middleware` | `['web', 'auth']` | Middleware applied to dashboard routes; supports application middleware such as `super-admin` and `super-ip` |
| `layout` | `null` | Optional Blade layout to extend |
| `composer_binary` | `composer` | Composer executable path |
| `timeout` | `120` | Composer process timeout in seconds |
| `blocker_limit` | `30` | Maximum outdated packages checked for update blockers |
| `cache.store` | Default | Optional Laravel cache store |
| `cache.key` | `composer-monitor:latest` | Cache key |
| `cache.ttl` | `null` | Cache lifetime; null keeps the latest result |

## Access control

The dashboard uses the `web` and `auth` middleware by default. You can customize the middleware in `config/composer-monitor.php`. For additional application-specific authorization, register a callback using the facade in a service provider:

```php
use Illuminate\\Http\\Request;
use MertAydin\\ComposerMonitor\\Facades\\ComposerMonitor;

public function boot(): void
{
    ComposerMonitor::auth(fn (Request $request): bool =>
        $request->user()?->hasRole('SuperAdmin') ?? false
    );
}
```

The callback receives the current request. Return `true` to allow access or `false` to respond with HTTP 403. The callback is optional; the configured route middleware still applies.

The dashboard exposes dependency and security information. Restrict access to trusted users by configuring middleware in `config/composer-monitor.php`:

```php
'route' => [
    'middleware' => ['web', 'auth'],
],
```

For production applications, do not expose the dashboard publicly.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).


The dashboard is accessible to users who pass the configured route middleware when no authorization Gate is defined. If `viewComposerMonitor` is defined, Laravel enforces it on every dashboard route. Change the ability name with `authorization.gate`. The existing `ComposerMonitor::auth(...)` callback remains available as an additional authorization restriction.
