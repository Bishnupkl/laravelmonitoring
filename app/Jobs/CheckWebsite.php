<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\WebsiteChecker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 15;

    public function __construct(public Website $website)
    {
    }

    public function handle(WebsiteChecker $checker): void
    {

        Log::debug("Starting check for {$this->website->url}, attempt {$this->attempts()} of {$this->tries}");

        $result = $checker->check($this->website->url);
        Log::debug("Check result for {$this->website->url}: " . json_encode($result));

        if ($result['exists'] && !$result['is_up']) {
            $this->sendAlert($result['error']);
        } else {
            Log::info("No alert sent for {$this->website->url}: exists={$result['exists']}, is_up={$result['is_up']}");
        }

        $this->website->update([
            'is_down' => !$result['is_up'],
            'last_checked_at' => now(),
            'retry_count' => !$result['is_up'] ? $this->website->retry_count + 1 : 0,
        ]);

        if ($result['error'] && $this->attempts() >= $this->tries) {
            Log::error("Job failed after {$this->tries} attempts for {$this->website->url}: {$result['error']}");
            $this->fail(new \Exception($result['error']));
        }
    }

    protected function sendAlert(): void
    {
        $message ="{$this->website->url} is down!";


        Log::info("Sending email for {$this->website->url} to {$this->website->client->email}: $message");
        try {
            Mail::raw($message, function ($message) {
                $message->to($this->website->client->email)
                    ->subject("{$this->website->url} is down!")
                    ->from('do-not-reply@example.com', 'Uptime Monitor');
            });
            Log::info("Email sent successfully for {$this->website->url}");
        } catch (\Exception $e) {
            Log::error("Failed to send email for {$this->website->url}: {$e->getMessage()}");
        }
    }
}
