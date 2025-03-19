<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ConnectException;


class WebsiteChecker
{
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 10.0,         // Enforce 1-second timeout (1000ms)
            'connect_timeout' => 10.0, // Ensure connection doesn’t exceed 1 second
            'http_errors' => false,   // Prevent Guzzle from throwing exceptions on 4xx/5xx

        ]);
    }

    public function check(string $url): array
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            Log::warning("Invalid URL format: {$url}");
            return ['exists' => false, 'is_up' => false, 'error' => 'Invalid URL format'];
        }

//        $ip = gethostbyname($domain);
//        Log::info("Domain IP: {$ip}");
//
//
//        if ($ip === $domain) {
//            Log::info("Domain does not exist: {$domain}");
//            return ['exists' => false, 'is_up' => false, 'error' => 'Domain does not exist'];
//        }

        try {
            // Perform the HTTP request with a strict 1-second limit
            $response = $this->httpClient->get($url); // Line 38: Updated timeout applies here
            $status = $response->getStatusCode() === 200;

            return ['exists' => true, 'is_up' => $status, 'error' => null];
        } catch (RequestException $e) {
            if ($e->getCode() === 28 || str_contains($e->getMessage(), 'cURL error 28')) {
                Log::warning("Timeout detected for {$url}: {$e->getMessage()}");
                return ['exists' => true, 'is_up' => false, 'error' => 'Connection timed out after 1 second'];
            }
            Log::warning("Failed to check {$url}: {$e->getMessage()}");
            return ['exists' => true, 'is_up' => false, 'error' => $e->getMessage()];
        } catch (ConnectException $e) {
            Log::warning("Failed to check {$url}: {$e}");
            return ['exists' => true, 'is_up' => false, 'error' => $e->getMessage()];
        }
        catch (GuzzleException $e) {
            Log::error("Failed to check {$url}: {$e}");
            return ['exists' => true, 'is_up' => false, 'error' => $e->getMessage()];
        }
    }
}
