<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebsiteFactory extends Factory
{
    protected $model = Website::class;

    public function definition()
    {
        return [
            'client_id' => Client::factory(), // Automatically creates a client before assigning it
            'url' => $this->faker->url,
        ];
    }
}
