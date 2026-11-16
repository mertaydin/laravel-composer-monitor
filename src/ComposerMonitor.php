<?php

namespace MertAydin\ComposerMonitor;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

class ComposerMonitor
{
    public function packages(): array
    {
        $lockPath = base_path('composer.lock');
        $manifestPath = base_path('composer.json');

        if (! is_file($lockPath) || ! is_file($manifestPath)) {
            throw new RuntimeException('composer.json and composer.lock must exist in the application root.');
        }

        $lock = json_decode(file_get_contents($lockPath), true, 512, JSON_THROW_ON_ERROR);
        $manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        $direct = array_merge($manifest['require'] ?? [], $manifest['require-dev'] ?? []);
        unset($direct['php']);

        foreach (array_keys($direct) as $name) {
            if (str_starts_with($name, 'ext-')) {
                unset($direct[$name]);
            }
        }

        $devPackages = array_column($lock['packages-dev'] ?? [], 'name');
        $packages = [];

        foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $package) {
            if (empty($package['name'])) {
                continue;
            }

            $name = $package['name'];
            $packages[$name] = [
                'name' => $name,
                'version' => $package['version'] ?? 'unknown',
                'description' => $package['description'] ?? '',
                'direct' => array_key_exists($name, $direct),
                'dev' => in_array($name, $devPackages, true),
                'url' => $package['homepage'] ?? $package['source']['url'] ?? null,
                'released_at' => $package['time'] ?? null,
            ];
        }

        ksort($packages);

        return $packages;
    }

    public function latest(): ?array
    {
        return $this->cache()->get($this->cacheKey());
    }

    public function issues(?array $latest = null): ?array
    {
        $latest ??= $this->latest();

        if (! $latest || isset($latest['error'])) {
            return null;
        }

        return [
            'outdated' => count($latest['outdated'] ?? []),
            'vulnerable' => count($latest['advisories'] ?? []),
            'abandoned' => count($latest['abandoned'] ?? []),
            'advisories' => array_sum(array_map(
                fn (array $item) => count($item['advisories'] ?? []),
                $latest['advisories'] ?? []
            )),
        ];
    }

    public function check(): array
    {
        $previous = $this->latest();

        try {
            $auditData = $this->decode($this->run('audit', '--locked', '--format=json'));
            $outdatedData = $this->decode($this->run('outdated', '--locked', '--format=json'));

            $advisories = [];
            foreach ($auditData['advisories'] ?? [] as $name => $items) {
                $advisories[$name] = [
                    'name' => $name,
                    'advisories' => array_map(fn (array $item) => [
                        'title' => $item['title'] ?? $item['advisoryId'] ?? __('composer-monitor::messages.security_advisory'),
                        'cve' => $item['cve'] ?? null,
                        'advisory_id' => $item['advisoryId'] ?? null,
                        'link' => $item['link'] ?? null,
                        'severity' => strtolower($item['severity'] ?? 'unknown'),
                        'affected_versions' => $item['affectedVersions'] ?? null,
                        'reported_at' => $item['reportedAt'] ?? null,
                    ], $items),
                ];
            }

            $abandoned = [];
            foreach ($auditData['abandoned'] ?? [] as $name => $replacement) {
                $abandoned[] = ['name' => $name, 'replacement' => is_string($replacement) ? $replacement : null];
            }

            $updates = [];
            foreach ($outdatedData['installed'] ?? $outdatedData['locked'] ?? [] as $item) {
                if (empty($item['name'])) {
                    continue;
                }

                $updates[$item['name']] = [
                    'name' => $item['name'],
                    'version' => $item['version'] ?? 'unknown',
                    'latest' => $item['latest'] ?? null,
                    'latest_status' => $item['latest-status'] ?? null,
                    'latest_release_date' => $item['latest-release-date'] ?? null,
                    'description' => $item['description'] ?? '',
                    'status' => $item['latest-status'] ?? null,
                    'blockers' => [],
                ];
            }

            $limit = (int) config('composer-monitor.blocker_limit', 30);
            $checked = 0;

            foreach ($updates as $name => &$item) {
                if ($checked >= $limit || $item['status'] !== 'update-possible' || empty($item['latest'])) {
                    continue;
                }

                $item['blockers'] = $this->blockers($name, $item['latest']);
                $checked++;
            }
            unset($item);

            $result = [
                'checked_at' => now()->toIso8601String(),
                'outdated' => array_values($updates),
                'advisories' => array_values($advisories),
                'abandoned' => $abandoned,
            ];
        } catch (Throwable $exception) {
            if ($previous && ! isset($previous['error'])) {
                $previous['error'] = $exception->getMessage();
                $previous['error_at'] = now()->toIso8601String();
                $this->cache()->forever($this->cacheKey(), $previous);

                return $previous;
            }

            $result = [
                'checked_at' => now()->toIso8601String(),
                'outdated' => [],
                'advisories' => [],
                'abandoned' => [],
                'error' => $exception->getMessage(),
                'error_at' => now()->toIso8601String(),
            ];
        }

        $ttl = config('composer-monitor.cache.ttl');

        if ($ttl) {
            $this->cache()->put($this->cacheKey(), $result, $ttl);
        } else {
            $this->cache()->forever($this->cacheKey(), $result);
        }

        return $result;
    }

    protected function blockers(string $name, string $version): array
    {
        try {
            $output = $this->run('why-not', $name, $version);
        } catch (Throwable) {
            return [];
        }

        $blockers = [];

        foreach (preg_split('/\r?\n/', $output) as $line) {
            if (preg_match('/^\s*([^\s]+)\s+([^\s]+)\s+(requires|conflicts with)\s+(.+)$/i', trim($line), $matches)) {
                $blockers[] = [
                    'package' => $matches[1],
                    'version' => $matches[2],
                    'relation' => strtolower($matches[3]),
                    'constraint' => $matches[4],
                ];
            }
        }

        return $blockers;
    }

    protected function run(string ...$arguments): string
    {
        $home = config('composer-monitor.composer_home', storage_path('app/composer-monitor'));

        if (! is_dir($home) && ! mkdir($home, 0755, true) && ! is_dir($home)) {
            throw new RuntimeException('Unable to create Composer home directory.');
        }

        $binary = config('composer-monitor.composer_binary', 'composer');
        $result = Process::path(base_path())
            ->timeout((int) config('composer-monitor.timeout', 120))
            ->env([
                'HOME' => $home,
                'COMPOSER_HOME' => $home,
                'COMPOSER_NO_INTERACTION' => '1',
            ])
            ->run(array_merge([$binary], $arguments, ['--no-interaction']));

        $output = trim($result->output());

        if ($output === '') {
            $output = trim($result->errorOutput());
        }

        if ($output === '') {
            throw new RuntimeException('Composer command returned no output: '.implode(' ', $arguments));
        }

        return $output;
    }

    protected function decode(string $output): array
    {
        $data = json_decode($output, true);

        if (! is_array($data)) {
            throw new RuntimeException('Composer returned an invalid JSON response.');
        }

        return $data;
    }

    protected function cache()
    {
        $store = config('composer-monitor.cache.store');

        return $store ? Cache::store($store) : Cache::store();
    }

    protected function cacheKey(): string
    {
        return config('composer-monitor.cache.key', 'composer-monitor:latest');
    }
}
