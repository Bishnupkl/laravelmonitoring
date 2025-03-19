<?php

namespace Feature;

use App\Jobs\CheckWebsite;
use App\Models\Client;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckWebsitesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_check_website_jobs_for_each_website()
    {
        // Fake the queue
        Queue::fake();
        $client = Client::factory()->create();

        // Create sample websites
        Website::factory()->count(3)->create([
            'client_id' => $client->id, // Ensuring valid foreign key reference
        ]);
        // Run the command
        $this->artisan('websites:check')
            ->expectsOutput('Website checks dispatched to queue.')
            ->assertExitCode(0);

        // Assert that jobs were dispatched for each website
        Queue::assertPushed(CheckWebsite::class, 3);
    }
}
