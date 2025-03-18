<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Website;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder {
    /**
     * Run the database seeds.
     */

    public function run(): void {
        $client1 = Client::create(['email' => 'client1@example.com']);
        $client2 = Client::create(['email' => 'client2@example.com']);
        $client3 = Client::create(['email' => 'client3@example.com']);

        Website::create(['client_id' => $client1->id, 'url' => 'https://google.com']);
        Website::create(['client_id' => $client1->id, 'url' => 'https://sakai.miu.edu']);
        Website::create(['client_id' => $client2->id, 'url' => 'https://example.com']);
        Website::create(['client_id' => $client2->id, 'url' => 'https://facebook.com']);
        Website::create(['client_id' => $client3->id, 'url' => 'https://linux.com']);
        Website::create(['client_id' => $client3->id, 'url' => 'https://laravel.com']);
    }

}
