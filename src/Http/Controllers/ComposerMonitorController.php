<?php

namespace MertAydin\ComposerMonitor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MertAydin\ComposerMonitor\ComposerMonitor;
use Throwable;

class ComposerMonitorController extends Controller
{
    private const SUPPORTED_LOCALES = ['tr', 'en', 'ar', 'es', 'hi', 'zh_CN'];

    public function index(ComposerMonitor $monitor)
    {
        if (session()->has('composer-monitor.locale')) {
            app()->setLocale(session('composer-monitor.locale'));
        }

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

    public function showPackage(Request $request, ComposerMonitor $monitor)
    {
        $package = $this->validatedPackage($request);

        return $this->runCommand(fn () => $monitor->showPackage($package));
    }

    public function whyPackage(Request $request, ComposerMonitor $monitor)
    {
        $package = $this->validatedPackage($request);

        return $this->runCommand(fn () => $monitor->whyPackage($package));
    }

    public function audit(ComposerMonitor $monitor)
    {
        return $this->runCommand(fn () => $monitor->auditReport());
    }

    public function suggests(ComposerMonitor $monitor)
    {
        return $this->runCommand(fn () => $monitor->suggestions());
    }

    protected function validatedPackage(Request $request): string
    {
        return $request->validate([
            'package' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_.-]+\/[a-z0-9_.-]+$/i'],
        ])['package'];
    }

    protected function runCommand(callable $callback)
    {
        try {
            return response()->json(['success' => true] + $callback());
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function preferences(Request $request)
    {
        $locale = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', self::SUPPORTED_LOCALES)],
        ])['locale'];

        session(['composer-monitor.locale' => $locale]);
        app()->setLocale($locale);

        return redirect()->back();
    }
}
