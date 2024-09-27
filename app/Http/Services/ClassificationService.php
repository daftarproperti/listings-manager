<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class ClassificationService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = type(config('services.msg_classification.endpoint'))->asString();
    }

    public function classify(string $text): string
    {
        $retryAttempts = 3;
        $retryDelay = 2;
        $maxDelay = 64;

        for ($attempt = 0; $attempt < $retryAttempts; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])
                    ->post($this->apiUrl, [
                        'text' => $text
                    ]);

                if (!$response->successful()) {
                    throw new \ErrorException($response->body());
                }

                /** @var array<string> $responseData */
                $responseData = $response->json();
                return $responseData['result'];
            } catch (RequestException $e) {
                Log::error('Error occurred while making HTTP request: ' . $e->getMessage());
            }

            $delay = min($retryDelay * 2 ** $attempt, $maxDelay);
            Log::info('Retrying in ' . $delay . ' seconds');
            sleep($delay);
        }

        throw new \ErrorException('Failed to get response after ' . $retryAttempts . ' attempts.');
    }
}
