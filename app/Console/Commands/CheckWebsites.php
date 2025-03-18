<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsite;
use App\Models\Website;
use Illuminate\Console\Command;

class CheckWebsites extends Command
{
    protected $signature = 'websites:check';
    protected $description = 'Dispatch website uptime checks';

    public function handle(): void
    {
        Website::chunk(100, function ($websites) {
            foreach ($websites as $website) {
                CheckWebsite::dispatch($website)->onQueue('website_checks');
            }
        });
        $this->info('Website checks dispatched to queue.');
    }
}
