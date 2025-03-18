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

class CheckWebsite implements ShouldQueue {
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

public $tries = 3;
public $timeout = 15;

public function __construct(public Website $website) {}

public function handle(WebsiteChecker $checker): void {
$result = $checker->check($this->website->url);

if ($result['exists'] && !$result['is_up']) {
$this->sendAlert($result['error']);
}

$this->website->update([
'is_down' => !$result['is_up'],
'last_checked_at' => now(),
'retry_count' => !$result['is_up'] ? $this->website->retry_count + 1 : 0,
]);

if ($result['error'] && $this->attempts() >= $this->tries) {
$this->fail(new \Exception($result['error']));
}
}

protected function sendAlert(?string $error = null): void {
$message = $error && str_contains($error, 'cURL error 28')
? "{$this->website->url} is down due to a timeout!"
: "{$this->website->url} is down!";

Log::info("Sending email for {$this->website->url} to {$this->website->client->email}: $message");
Mail::raw($message, function ($message) {
$message->to($this->website->client->email)
->subject("{$this->website->url} is down!")
->from('do-not-reply@example.com', 'Uptime Monitor');
});
}
}
