<?php

// app/Services/WebsiteChecker.php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WebsiteChecker
{
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client(['timeout' => 10]);
    }

    public function check(string $url): array
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            return ['exists' => false, 'is_up' => false, 'error' => 'Invalid URL format'];
        }

        $ip = gethostbyname($domain);
        if ($ip === $domain) {
            return ['exists' => false, 'is_up' => false, 'error' => 'Domain does not exist'];
        }

        try {
            $response = $this->httpClient->get($url);
            $status = $response->getStatusCode() === 200;
            return ['exists' => true, 'is_up' => $status, 'error' => null];
        } catch (RequestException $e) {
            Log::warning("Failed to check {$url}: {$e->getMessage()}");
            return ['exists' => true, 'is_up' => false, 'error' => $e->getMessage()];
        }
    }
}
