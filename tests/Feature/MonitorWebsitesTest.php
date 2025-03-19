<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\MonitorWebsites;
use App\Jobs\CheckWebsite;
use App\Models\Client;
use App\Models\Website;
use Database\Factories\WebsiteFactory;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;
use Tests\TestCase;

class MonitorWebsitesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_check_website_jobs_when_not_locked()
    {
        Queue::fake();
        Cache::shouldReceive('lock')->once()->andReturn(new class {
            public function get()
            {
                return true;
            }

            public function release()
            {
            }
        });

        Website::factory()->count(3)->create();

        $this->artisan('websites:monitor')
            ->expectsOutput('Starting website monitoring daemon...')
            ->expectsOutput('Checking websites at ' . now()->toDateTimeString())
            ->expectsOutput('Website checks dispatched to queue.')
            ->assertExitCode(0);

        Queue::assertPushed(CheckWebsite::class, 3);
    }

    /** @test */
    public function it_skips_execution_if_already_locked()
    {
        Cache::shouldReceive('lock')->once()->andReturn(new class {
            public function get()
            {
                return false;
            }
        });

        $this->artisan('websites:monitor')
            ->expectsOutput('Starting website monitoring daemon...')
            ->expectsOutput('Monitoring already in progress, skipping this cycle.')
            ->assertExitCode(0);
    }

    /** @test */
    /** @test */
    public function it_logs_error_if_monitoring_fails()
    {
        $client = Client::factory()->create();
        Website::factory()->create(['client_id' => $client->id, "url" => "https://sakai.miu.edu"]);




//        Queue::fake();

        Cache::shouldReceive('lock')->andReturn(new class {
            public function get()
            {
                return true;
            }

            public function release()
            {
            }
        });

        $this->partialMock(Website::class, function ($mock) {
            $mock->shouldReceive('chunk')->andThrow(new \Exception('Test error'));
        });
//

        Log::shouldReceive('error')->once();

        $this->artisan('websites:monitor')
            ->assertExitCode(0);


    }

}
