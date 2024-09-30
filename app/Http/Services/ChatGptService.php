<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class ChatGptService
{
    private string $apiKey;
    private string $apiUrl;
    private string $modelVersion;

    public function __construct()
    {
        $this->apiKey = type(config('services.chatgpt.key'))->asString();
        $this->apiUrl = type(config('services.chatgpt.endpoint'))->asString();
        $this->modelVersion = type(config('services.chatgpt.model_version'))->asString();
    }

    public function seekAnswerWithRetry(string $question): string
    {
        $retryAttempts = 3;
        $retryDelay = 2;
        $maxDelay = 64;

        for ($attempt = 0; $attempt < $retryAttempts; $attempt++) {
            try {
                $answer = $this->seekAnswer($question);
                if (json_validate($answer)) {
                    return $answer;
                }
            } catch (RequestException $e) {
                Log::error('Error occurred while making HTTP request: ' . $e->getMessage());
            }

            $delay = min($retryDelay * 2 ** $attempt, $maxDelay);
            Log::info('Retrying in ' . $delay . ' seconds');
            sleep($delay);
        }

        throw new \ErrorException('Failed to get response after ' . $retryAttempts . ' attempts.');
    }

    public function seekAnswer(string $question, string $model = null): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->post($this->apiUrl, [
                'model' => $model ?? $this->modelVersion,
                'messages' => [
                    ['role' => 'user', 'content' => $question],
                ],
            ]);

        if (!$response->successful()) {
            throw new \ErrorException($response->body());
        }

        /** @var array<array<array<array<string>>>> $responseData */
        $responseData = $response->json();
        return $responseData['choices'][0]['message']['content'];
    }

    /**
     * @param array<array<mixed>> $messagesRole
     */
    public function seekAnswerWihtCustomMessagesRole(array $messagesRole, string $model = null): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->post($this->apiUrl, [
                'model' => $model ?? $this->modelVersion,
                'messages' => $messagesRole,
            ]);

        if (!$response->successful()) {
            throw new \ErrorException($response->body());
        }

        /** @var array<array<array<array<string>>>> $responseData */
        $responseData = $response->json();
        return $responseData['choices'][0]['message']['content'];
    }
}
