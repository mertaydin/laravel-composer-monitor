<?php

namespace MertAydin\ComposerMonitor\Tests\Feature;

use MertAydin\ComposerMonitor\Tests\TestCase;

class ConfigurableRoutePrefixTest extends TestCase
{
    public function test_dashboard_route_prefix_supports_nested_paths_and_slash_normalization(): void
    {
        config([
            'composer-monitor.route.prefix' => '/panel/super/composer-monitor/',
            'composer-monitor.route.middleware' => ['web', 'super-admin', 'super-ip'],
        ]);

        require dirname(__DIR__, 2).'/routes/web.php';

        $route = collect(app('router')->getRoutes())->first(
            fn ($route): bool => $route->getName() === 'composer-monitor.index'
                && $route->uri() === 'panel/super/composer-monitor'
        );

        $this->assertNotNull($route);
        $this->assertContains('super-admin', $route->middleware());
        $this->assertContains('super-ip', $route->middleware());
    }
}
