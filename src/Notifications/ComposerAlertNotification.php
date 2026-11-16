<?php

namespace MertAydin\ComposerMonitor\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComposerAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public array $alert) {}

    public function via(object $notifiable): array
    {
        return array_values(array_intersect(
            (array) config('composer-monitor.alerts.channels', ['mail', 'database']),
            ['mail', 'database']
        ));
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Composer Monitor] '.$this->alert['title'])
            ->greeting('Composer Monitor')
            ->line($this->alert['message'])
            ->action('Open Composer Monitor', url(config('composer-monitor.route.prefix', 'composer-monitor')));
    }

    public function toArray(object $notifiable): array
    {
        return $this->alert;
    }
}
