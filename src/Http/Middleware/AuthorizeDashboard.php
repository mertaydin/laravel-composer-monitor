<?php

namespace MertAydin\ComposerMonitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use MertAydin\ComposerMonitor\Services\DashboardAuthorizer;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeDashboard
{
    public function __construct(private readonly DashboardAuthorizer $authorizer) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ability = (string) config('composer-monitor.authorization.gate', 'viewComposerMonitor');

        if (Gate::has($ability)) {
            Gate::authorize($ability);
        }

        if ($this->authorizer->allows($request) === false) {
            abort(403, 'You are not authorized to access the Composer monitoring dashboard.');
        }

        return $next($request);
    }
}
