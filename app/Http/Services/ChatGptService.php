<?php

namespace App\Http\Services;

use App\Helpers\Assert;
use App\Models\Listing;
use App\Models\ListingUser;
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
            $errorCode = $response->status();
            $errorMessage = $response->body();
            return $errorMessage . ' (' . $errorCode . ')';
        }

        /** @var array<array<array<array<string>>>> $responseData */
        $responseData = $response->json();
        return $responseData['choices'][0]['message']['content'];
    }

    /**
     * @param array<mixed> $data
     */
    public function saveAnswer(array $data, ListingUser $user = null): ?Listing
    {
        $listing = new Listing();

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $listing->$key = $this->formatValue($key, $value);
            } else {
                $listing->$key = $value;
            }
        }

        if ($user) {
            $listing->user = $user;
        }

        //set default to public view
        $listing->isPrivate = false;

        $listing->save();

        return $listing;
    }

    private function formatValue(string $key, string $value): int|float|string
    {
        $integerFields = ['lotSize', 'buildingSize', 'carCount', 'bedroomCount', 'bathroomCount', 'floorCount', 'electricPower'];
        $floatFields = ['price'];

        if (in_array($key, $integerFields)) {
            return (int) $value;
        }

        if (in_array($key, $floatFields)) {
            return (float) $value;
        }

        return $value;
    }
}
