<?php

namespace MertAydin\ComposerMonitor\Tests\Feature;

use MertAydin\ComposerMonitor\ComposerAlertManager;
use MertAydin\ComposerMonitor\Tests\TestCase;

class RoutesTest extends TestCase
{
    public function test_dashboard_routes_are_registered(): void
    {
        $this->assertTrue(app('router')->has('composer-monitor.index'));
        $this->assertTrue(app('router')->has('composer-monitor.check'));
        $this->assertTrue(app('router')->has('composer-monitor.preferences'));

        $middleware = app('router')->getRoutes()->getByName('composer-monitor.index')->gatherMiddleware();
        $this->assertContains('auth', $middleware);
    }

    public function test_dashboard_views_are_registered(): void
    {
        foreach (['index', 'dashboard', 'standalone', 'embedded'] as $view) {
            $this->assertTrue(app('view')->exists('composer-monitor::'.$view));
        }
    }

    public function test_dashboard_renders_without_a_custom_layout(): void
    {
        config(['composer-monitor.layout' => null]);

        $html = view('composer-monitor::index', [
            'packages' => [],
            'latest' => [],
            'outdated' => [],
            'advisories' => [],
            'abandoned' => [],
            'alerts' => [],
        ])->render();

        $this->assertStringContainsString('<!doctype html>', $html);
        $this->assertStringContainsString('Composer Monitor', $html);
        $this->assertStringContainsString('Dependency alerts', $html);
        $this->assertStringContainsString('id="composer-monitor-page-loading"', $html);
        $this->assertStringContainsString('class="page-loading-card"', $html);
        $this->assertStringContainsString('class="page-loading-bar"', $html);
    }

    public function test_dashboard_uses_a_published_external_stylesheet_without_inline_styles(): void
    {
        $html = view('composer-monitor::index', [
            'packages' => [],
            'latest' => [],
            'outdated' => [],
            'advisories' => [],
            'abandoned' => [],
            'alerts' => [],
        ])->render();

        $cssAsset = preg_quote(asset('vendor/composer-monitor/css/app.css'), '/');
        $this->assertSame(1, preg_match('/href="'.$cssAsset.'\\?v=([0-9.]+)"/', $html, $cssMatch));
        $this->assertStringNotContainsString('<style', $html);
        $this->assertDoesNotMatchRegularExpression('/\\sstyle\\s*=/', $html);
        $this->assertStringContainsString('src="'.asset('vendor/composer-monitor/js/app.js').'?v='.$cssMatch[1].'"', $html);
        $this->assertDoesNotMatchRegularExpression('/\\son(click|change|input|submit)\\s*=/', $html);
    }

    public function test_dashboard_offers_all_six_languages(): void
    {
        $html = view('composer-monitor::dashboard', [
            'packages' => [],
            'latest' => [],
            'outdated' => [],
            'advisories' => [],
            'abandoned' => [],
            'alerts' => [],
        ])->render();

        foreach (['tr', 'en', 'ar', 'es', 'hi', 'zh_CN'] as $locale) {
            $this->assertStringContainsString('value="'.$locale.'"', $html);
            $this->assertFileExists(dirname(__DIR__, 2).'/lang/'.$locale.'/messages.php');
        }
    }

    public function test_alert_manager_rearms_after_the_condition_clears(): void
    {
        config([
            'composer-monitor.alerts.enabled' => true,
            'composer-monitor.alerts.outdated_threshold' => 1,
            'composer-monitor.alerts.channels' => ['dashboard'],
        ]);
        cache()->flush();

        $result = ['outdated' => [['name' => 'vendor/package', 'latest' => '2.0']]];
        $manager = app(ComposerAlertManager::class);

        $this->assertCount(1, $manager->evaluate($result));
        $this->assertCount(0, $manager->evaluate($result));
        $this->assertCount(0, $manager->evaluate(['outdated' => []]));
        $this->assertCount(1, $manager->evaluate($result));
    }
}
