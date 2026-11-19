<?php

namespace MertAydin\ComposerMonitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MertAydin\ComposerMonitor\Services\DashboardAuthorizer;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeDashboard
{
    public function __construct(private readonly DashboardAuthorizer $authorizer) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->authorizer->allows($request) === false) {
            abort(403, 'You are not authorized to access the Composer monitoring dashboard.');
        }

        return $next($request);
    }
}
