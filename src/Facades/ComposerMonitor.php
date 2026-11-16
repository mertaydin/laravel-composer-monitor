<?php

namespace MertAydin\ComposerMonitor\Facades;

use Illuminate\Support\Facades\Facade;
use MertAydin\ComposerMonitor\Services\DashboardAuthorizer;

/**
 * @method static void auth(callable $callback)
 *
 * @see DashboardAuthorizer
 */
class ComposerMonitor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DashboardAuthorizer::class;
    }
}
