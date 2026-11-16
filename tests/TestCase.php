<?php

namespace MertAydin\ComposerMonitor\Tests;

use MertAydin\ComposerMonitor\ComposerMonitorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ComposerMonitorServiceProvider::class];
    }
}
