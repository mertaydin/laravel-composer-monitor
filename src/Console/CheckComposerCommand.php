<?php

namespace MertAydin\ComposerMonitor\Console;

use Illuminate\Console\Command;
use MertAydin\ComposerMonitor\ComposerMonitor;

class CheckComposerCommand extends Command
{
    protected $signature = 'composer-monitor:check';

    protected $description = 'Check Composer dependencies for updates and security advisories.';

    public function handle(ComposerMonitor $monitor): int
    {
        $result = $monitor->check();

        if (isset($result['error'])) {
            $this->error($result['error']);

            return self::FAILURE;
        }

        $this->info('Composer dependency check completed.');
        $this->line('Outdated: '.count($result['outdated']));
        $this->line('Packages with advisories: '.count($result['advisories']));
        $this->line('Abandoned: '.count($result['abandoned']));

        return self::SUCCESS;
    }
}
