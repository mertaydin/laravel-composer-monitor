<?php

namespace MertAydin\ComposerMonitor\Tests\Feature;

use MertAydin\ComposerMonitor\Tests\TestCase;

class RoutesTest extends TestCase
{
    public function test_dashboard_routes_are_registered(): void
    {
        $this->assertTrue(app('router')->has('composer-monitor.index'));
        $this->assertTrue(app('router')->has('composer-monitor.check'));
        $this->assertTrue(app('router')->has('composer-monitor.preferences'));
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
            'packages' => [], 'latest' => [], 'outdated' => [], 'advisories' => [], 'abandoned' => [], 'alerts' => [],
        ])->render();
        $this->assertStringContainsString('<!doctype html>', $html);
        $this->assertStringContainsString('Composer Monitor', $html);
        $this->assertStringContainsString('Dependency alerts', $html);
    }

    public function test_alert_manager_deduplicates_repeated_alerts(): void
    {
        config(['composer-monitor.alerts.enabled' => true, 'composer-monitor.alerts.outdated_threshold' => 1, 'composer-monitor.alerts.channels' => ['dashboard']]);
        cache()->flush();
        $result = ['outdated' => [['name' => 'vendor/package', 'latest' => '2.0']]];
        $manager = app(\MertAydin\ComposerMonitor\ComposerAlertManager::class);
        $this->assertCount(1, $manager->evaluate($result));
        $this->assertCount(0, $manager->evaluate($result));
    }
}
