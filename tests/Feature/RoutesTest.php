<?php

namespace MertAydin\ComposerMonitor\Tests\Feature;

use MertAydin\ComposerMonitor\Tests\TestCase;

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
    }
}
