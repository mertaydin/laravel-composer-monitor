<?php

namespace MertAydin\ComposerMonitor\Tests\Feature;

use Illuminate\Http\Request;
use MertAydin\ComposerMonitor\Facades\ComposerMonitor;
use MertAydin\ComposerMonitor\Http\Middleware\AuthorizeDashboard;
use MertAydin\ComposerMonitor\Services\DashboardAuthorizer;
use MertAydin\ComposerMonitor\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DashboardAuthorizationTest extends TestCase
{
    public function test_authorization_callback_can_allow_access(): void
    {
        ComposerMonitor::auth(fn (Request $request): bool => $request->user()?->hasRole('SuperAdmin') ?? false);

        $request = Request::create('/composer-monitor');
        $request->setUserResolver(fn () => new class
        {
            public function hasRole(string $role): bool
            {
                return $role === 'SuperAdmin';
            }
        });

        $response = app(AuthorizeDashboard::class)->handle($request, fn () => response('allowed'));

        $this->assertSame('allowed', $response->getContent());
    }

    public function test_authorization_callback_can_deny_access(): void
    {
        ComposerMonitor::auth(fn (): bool => false);

        $this->expectException(HttpException::class);

        app(AuthorizeDashboard::class)->handle(
            Request::create('/composer-monitor'),
            fn () => response('allowed')
        );
    }

    public function test_authorization_denies_access_when_no_callback_is_registered(): void
    {
        $authorizer = app(DashboardAuthorizer::class);

        $this->assertFalse($authorizer->isConfigured());

        $this->expectException(HttpException::class);

        app(AuthorizeDashboard::class)->handle(
            Request::create('/composer-monitor'),
            fn () => response('allowed')
        );
    }

    public function test_dashboard_routes_use_authentication_and_authorization_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('composer-monitor.index');
        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains(AuthorizeDashboard::class, $middleware);
    }
}
