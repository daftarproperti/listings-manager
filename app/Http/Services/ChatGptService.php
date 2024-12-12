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
    /**
     * @param string|array<int,array<string, mixed>> $question can be a simple string or a complex nested array
     *                                    depending on the question to ask
     * @param string $model AI model to use
     * @param array<string,mixed> $responseFormat can be as simple as ['type' => 'text'] or
     *                            ['type' => 'json_object'] but also the more complex
     *                            ['type' => 'json_schema', 'schema' => [...]]
     */
    public function seekAnswerWithRetry(
        string|array $question,
        string $model = null,
        array $responseFormat = ['type' => 'text'],
    ): string {
        $retryAttempts = 3;
        $retryDelay = 2;
        $maxDelay = 64;

        for ($attempt = 0; $attempt < $retryAttempts; $attempt++) {
            try {
                $answer = $this->seekAnswer($question, $model, $responseFormat);
                if (json_validate($answer)) {
                    return $answer;
                }
                Log::error("Answer not valid JSON:\n" . $answer);
            } catch (RequestException $e) {
                Log::error('Error occurred while making HTTP request: ' . $e->getMessage());
            }

            $delay = min($retryDelay * 2 ** $attempt, $maxDelay);
            Log::info('Retrying in ' . $delay . ' seconds');
            sleep($delay);
        }

        throw new \ErrorException('Failed to get response after ' . $retryAttempts . ' attempts.');
    }
    /**
     * @param string|array<int,array<string, mixed>> $question can be a simple string or a complex
     *                                    array depending on the question to ask
     * @param string $model AI model to use
     * @param array<string,mixed> $responseFormat can be as simple as ['type' => 'text'] or
     *                            ['type' => 'json_object'] but also the more complex
     *                            ['type' => 'json_schema', 'schema' => [...]]
     */
    public function seekAnswer(
        string|array $question,
        string $model = null,
        array $responseFormat = ['type' => 'text'],
    ): string {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->post($this->apiUrl, [
                'model' => $model ?? $this->modelVersion,
                'messages' => [
                    ['role' => 'user', 'content' => $question],
                ],
                'response_format' => $responseFormat,
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
