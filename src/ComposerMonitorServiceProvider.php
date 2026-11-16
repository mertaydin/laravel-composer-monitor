<?php

namespace MertAydin\ComposerMonitor;

use Illuminate\Support\ServiceProvider;
use MertAydin\ComposerMonitor\Console\CheckComposerCommand;

class ComposerMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/composer-monitor.php', 'composer-monitor');

        $this->app->singleton(ComposerMonitor::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'composer-monitor');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'composer-monitor');

        $this->publishes([
            __DIR__.'/../config/composer-monitor.php' => config_path('composer-monitor.php'),
        ], 'composer-monitor-config');

        if (config('composer-monitor.route.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([CheckComposerCommand::class]);
        }
    }
}
