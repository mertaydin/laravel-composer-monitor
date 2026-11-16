<?php

namespace MertAydin\ComposerMonitor;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use MertAydin\ComposerMonitor\Notifications\ComposerAlertNotification;
use Throwable;

class ComposerAlertManager
{
    public function evaluate(array $result): array
    {
        $rules = config('composer-monitor.alerts', []);
        if (! ($rules['enabled'] ?? false) || isset($result['error'])) {
            return [];
        }

        $updates = $result['outdated'] ?? [];
        $alerts = [];
        $threshold = (int) ($rules['outdated_threshold'] ?? 0);

        if ($threshold > 0 && count($updates) >= $threshold) {
            $alerts[] = [
                'type' => 'outdated_threshold',
                'title' => __('composer-monitor::messages.alert_threshold_title'),
                'message' => __('composer-monitor::messages.alert_threshold_message', ['count' => count($updates), 'threshold' => $threshold]),
            ];
        }

        foreach ((array) ($rules['packages'] ?? []) as $watched) {
            foreach ($updates as $package) {
                if (($package['name'] ?? null) !== $watched) {
                    continue;
                }

                $alerts[] = [
                    'type' => 'watched_package',
                    'package' => $watched,
                    'title' => __('composer-monitor::messages.alert_package_title', ['package' => $watched]),
                    'message' => __('composer-monitor::messages.alert_package_message', ['package' => $watched, 'version' => $package['latest'] ?? __('composer-monitor::messages.unknown_version')]),
                ];
            }
        }

        $fresh = [];
        foreach ($alerts as $alert) {
            $fingerprint = sha1(($alert['type'] ?? '').'|'.($alert['package'] ?? '').'|'.($alert['message'] ?? ''));
            $key = 'composer-monitor:alert-sent:'.$fingerprint;
            if (Cache::has($key)) {
                continue;
            }

            $dedupeTtl = (int) ($rules['dedupe_ttl'] ?? 86400);
            $dedupeTtl > 0 ? Cache::put($key, true, $dedupeTtl) : Cache::forever($key, true);
            $alert['id'] = $fingerprint;
            $alert['created_at'] = now()->toIso8601String();
            $fresh[] = $alert;
        }

        if ($fresh === []) {
            return [];
        }

        $historyKey = $rules['history_key'] ?? 'composer-monitor:alerts';
        Cache::forever($historyKey, array_slice(array_merge($fresh, Cache::get($historyKey, [])), 0, 50));

        foreach ($fresh as $alert) {
            $this->send($alert, $rules);
        }

        return $fresh;
    }

    protected function send(array $alert, array $rules): void
    {
        $channels = (array) ($rules['channels'] ?? ['dashboard']);

        if (in_array('mail', $channels, true)) {
            foreach ((array) ($rules['mail_to'] ?? []) as $address) {
                if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                try {
                    Notification::route('mail', $address)->notify(new ComposerAlertNotification($alert));
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }

        if (in_array('database', $channels, true)) {
            $users = (array) ($rules['users'] ?? []);
            $model = $rules['user_model'] ?? config('auth.providers.users.model');
            $userIds = (array) ($rules['user_ids'] ?? []);

            if ($userIds !== [] && is_string($model) && is_subclass_of($model, Model::class)) {
                $users = array_merge($users, $model::query()->whereKey($userIds)->get()->all());
            }

            foreach ($users as $user) {
                if (is_object($user) && method_exists($user, 'notify')) {
                    try {
                        $user->notify(new ComposerAlertNotification($alert));
                    } catch (Throwable $exception) {
                        report($exception);
                    }
                }
            }
        }

        if (in_array('slack', $channels, true) && filled($rules['slack_webhook'] ?? null)) {
            try {
                Http::timeout(10)->post($rules['slack_webhook'], ['text' => '*'.$alert['title']."*
".$alert['message']])->throw();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if (in_array('discord', $channels, true) && filled($rules['discord_webhook'] ?? null)) {
            try {
                Http::timeout(10)->post($rules['discord_webhook'], ['content' => '**'.$alert['title']."**
".$alert['message']])->throw();
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
