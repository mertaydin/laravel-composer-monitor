<?php

namespace MertAydin\\ComposerMonitor\\Tests\\Feature;

use MertAydin\\ComposerMonitor\\Tests\\TestCase;

class RoutesTest extends TestCase
{
    public function test_dashboard_routes_are_registered(): void
    {
        $this->assertTrue(app('router')->has('composer-monitor.index'));
        $this->assertTrue(app('router')->has('composer-monitor.check'));
    }

    public function test_dashboard_views_are_registered(): void
    {
        $this->assertTrue(app('view')->exists('composer-monitor::index'));
        $this->assertTrue(app('view')->exists('composer-monitor::dashboard'));
        $this->assertTrue(app('view')->exists('composer-monitor::standalone'));
        $this->assertTrue(app('view')->exists('composer-monitor::embedded'));
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
        ])->render();

        $this->assertStringContainsString('<!doctype html>', $html);
        $this->assertStringContainsString('Composer Monitor', $html);
    }
}
