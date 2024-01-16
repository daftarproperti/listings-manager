<?php

namespace App\Http\Services;

use App\Models\Property;
use Illuminate\Support\Facades\Http;

class ChatGptService
{
    private $apiKey;
    private $apiUrl;
    private $modelVersion;

    public function __construct()
    {
        $this->apiKey = config('services.chatgpt.key');
        $this->apiUrl = config('services.chatgpt.endpoint');
        $this->modelVersion = config('services.chatgpt.model_version');
    }

    public function seekAnswer(string $question): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->post($this->apiUrl, [
            'model' => $this->modelVersion,
            'messages' => [
                ['role' => 'user', 'content' => $question],
            ]
        ]);

        if (!$response->successful()) {
            $errorCode = $response->status();
            $errorMessage = $response->body();
            return $errorMessage.' ('.$errorCode.')';
        }

        $responseData = $response->json();
        return $responseData['choices'][0]['message']['content'];
    }

    public function saveAnswer(object $data, $user = null): ? Property
    {
        $property = new Property();

        foreach ($data as $key => $value) {
            $property->$key = $value;
        }

        if ($user) {
            $property->user = $user;
        }

        //set default to public view
        $property->isPrivate = false;

        $property->save();

        return $property;
    }
}
