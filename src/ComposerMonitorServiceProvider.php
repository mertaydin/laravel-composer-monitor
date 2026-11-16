<?php

namespace MertAydin\ComposerMonitor;

use Illuminate\Support\ServiceProvider;
use MertAydin\ComposerMonitor\Console\CheckComposerCommand;
use MertAydin\ComposerMonitor\Services\DashboardAuthorizer;

class ComposerMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/composer-monitor.php', 'composer-monitor');

        $this->app->singleton(ComposerMonitor::class);
        $this->app->singleton(ComposerAlertManager::class);
        $this->app->singleton(DashboardAuthorizer::class);
    }

    public function boot(): void
    {
        $viewPath = dirname(__DIR__).'/resources/views';

        $this->loadViewsFrom($viewPath, 'composer-monitor');
        $this->loadTranslationsFrom(dirname(__DIR__).'/lang', 'composer-monitor');

        $this->publishes([
            dirname(__DIR__).'/config/composer-monitor.php' => config_path('composer-monitor.php'),
        ], 'composer-monitor-config');

        $this->publishes([
            $viewPath => resource_path('views/vendor/composer-monitor'),
        ], 'composer-monitor-views');

        if (config('composer-monitor.route.enabled', true)) {
            $this->loadRoutesFrom(dirname(__DIR__).'/routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([CheckComposerCommand::class]);
        }
    }
}
