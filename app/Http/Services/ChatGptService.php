<?php

namespace App\Http\Services;

use App\Helpers\Assert;
use Illuminate\Support\Facades\Http;

class ChatGptService
{
    private string $apiKey;
    private string $apiUrl;
    private string $modelVersion;

    public function __construct()
    {
        $this->apiKey = Assert::string(config('services.chatgpt.key'));
        $this->apiUrl = Assert::string(config('services.chatgpt.endpoint'));
        $this->modelVersion = Assert::string(config('services.chatgpt.model_version'));
    }

    public function seekAnswer(string $question): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->post($this->apiUrl, [
                'model' => $this->modelVersion,
                'messages' => [
                    ['role' => 'user', 'content' => $question],
                ]
            ]);

        if (!$response->successful()) {
            throw new \ErrorException($response->body());
        }

        /** @var array<array<array<array<string>>>> $responseData */
        $responseData = $response->json();
        return $responseData['choices'][0]['message']['content'];
    }
}
