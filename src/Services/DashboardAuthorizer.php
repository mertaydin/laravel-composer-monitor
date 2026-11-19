<?php

namespace MertAydin\ComposerMonitor\Services;

use Closure;
use Illuminate\Http\Request;

class DashboardAuthorizer
{
    private ?Closure $callback = null;

    public function auth(callable $callback): void
    {
        $this->callback = Closure::fromCallable($callback);
    }

    public function isConfigured(): bool
    {
        return $this->callback !== null;
    }

    public function allows(Request $request): bool
    {
        // Dependency and security advisory data can expose application details.
        // Require an explicit host-application authorization callback.
        return $this->callback !== null && (bool) ($this->callback)($request);
    }
}
