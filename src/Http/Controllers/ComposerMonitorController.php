<?php

namespace MertAydin\ComposerMonitor\Http\Controllers;

use Illuminate\Routing\Controller;
use MertAydin\ComposerMonitor\ComposerMonitor;

class ComposerMonitorController extends Controller
{
    public function index(ComposerMonitor $monitor)
    {
        $packages = $monitor->packages();
        $latest = $monitor->latest() ?? [];
        $outdated = $latest['outdated'] ?? [];
        $advisories = $latest['advisories'] ?? [];
        $abandoned = $latest['abandoned'] ?? [];

        $layout = config('composer-monitor.layout');
        $view = is_string($layout) && trim($layout) !== ''
            ? 'composer-monitor::embedded'
            : 'composer-monitor::index';

        return view($view, compact(
            'packages',
            'latest',
            'outdated',
            'advisories',
            'abandoned'
        ));
    }

    public function check(ComposerMonitor $monitor)
    {
        $result = $monitor->check();
        $routeName = config('composer-monitor.route.name', 'composer-monitor.').'index';

        return redirect()->route($routeName)->with(
            isset($result['error']) ? 'error' : 'status',
            isset($result['error'])
                ? __('composer-monitor::messages.check_failed', ['message' => $result['error']])
                : __('composer-monitor::messages.check_complete')
        );
    }
}
