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
}
