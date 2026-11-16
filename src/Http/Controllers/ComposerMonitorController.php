<?php

namespace MertAydin\ComposerMonitor\Http\Controllers;

use Illuminate\Routing\Controller;
use MertAydin\ComposerMonitor\ComposerMonitor;

class ComposerMonitorController extends Controller
{
    public function __construct(protected ComposerMonitor $monitor)
    {
    }

    public function index()
    {
        $packages = $this->monitor->packages();
        $latest = $this->monitor->latest() ?? [];
        $outdated = $latest['outdated'] ?? [];
        $advisories = $latest['advisories'] ?? [];
        $abandoned = $latest['abandoned'] ?? [];

        return view('composer-monitor::index', compact(
            'packages',
            'latest',
            'outdated',
            'advisories',
            'abandoned'
        ));
    }

    public function check()
    {
        $result = $this->monitor->check();
        $routeName = config('composer-monitor.route.name', 'composer-monitor.').'index';

        return redirect()->route($routeName)->with(
            isset($result['error']) ? 'error' : 'status',
            isset($result['error'])
                ? __('composer-monitor::messages.check_failed', ['message' => $result['error']])
                : __('composer-monitor::messages.check_complete')
        );
    }
}
