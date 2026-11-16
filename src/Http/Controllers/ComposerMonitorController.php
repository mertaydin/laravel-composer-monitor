<?php

namespace MertAydin\ComposerMonitor\Http\Controllers;

use Illuminate\Http\Request;
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
        $alerts = cache()->get(config('composer-monitor.alerts.history_key', 'composer-monitor:alerts'), []);
        $layout = config('composer-monitor.layout');
        $view = is_string($layout) && trim($layout) !== '' ? 'composer-monitor::embedded' : 'composer-monitor::index';

        return view($view, compact('packages', 'latest', 'outdated', 'advisories', 'abandoned', 'alerts'));
    }

    public function check(ComposerMonitor $monitor)
    {
        $result = $monitor->check();
        $routeName = config('composer-monitor.route.name', 'composer-monitor.').'index';
        return redirect()->route($routeName)->with(
            isset($result['error']) ? 'error' : 'status',
            isset($result['error']) ? __('composer-monitor::messages.check_failed', ['message' => $result['error']]) : __('composer-monitor::messages.check_complete')
        );
    }

    public function preferences(Request $request)
    {
        $locale = $request->validate(['locale' => 'required|string|in:tr,en'])['locale'];
        session(['composer-monitor.locale' => $locale]);
        app()->setLocale($locale);
        return redirect()->back();
    }
}
