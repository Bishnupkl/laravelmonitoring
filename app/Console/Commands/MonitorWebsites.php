<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsite;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonitorWebsites extends Command {
    protected $signature = 'websites:monitor';
    protected $description = 'Monitor websites every 15 minutes without cron';

    public function handle(): void {
//        Log::info("Hello World");

        $this->info('Starting website monitoring daemon...');

        $isTesting = app()->environment('testing'); // Detect test environment
        $runOnce = $isTesting; // If in testing, run only once

        while (true) {
            $lock = Cache::lock('website_monitoring_lock', 10);

            if ($lock->get()) {
                try {
                    $this->info('Checking websites at ' . now()->toDateTimeString());

                    Website::chunk(100, function ($websites) {
                        foreach ($websites as $website) {
//                            echo $website->url . "\n";
                            CheckWebsite::dispatch($website)->onQueue('website_checks');
                        }
                    });

                    $this->info('Website checks dispatched to queue.');
                } catch (\Exception $e) {
                    Log::error("Monitoring failed: {$e->getMessage()}");
                    $this->error('An error occurred: ' . $e->getMessage());
                } finally {
                    $lock->release();
                }
            } else {
                $this->info('Monitoring already in progress, skipping this cycle.');
            }

            if ($runOnce) {
                break; // 🚀 Exit the loop in testing mode!
            }

            sleep(10);
        }
    }

}
