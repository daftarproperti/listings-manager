<?php

namespace App\Helpers;

use App\Http\Services\ChatGptService;
use Illuminate\Support\Facades\Log;

class Extractor
{
    private ChatGptService $chatGptService;

    public function __construct(ChatGptService $chatGptService) {
        $this->chatGptService = $chatGptService;
    }

    /**
     * @param string $message
     */
    public function generatePrompt($message): string
    {
        $template = storage_path('HousePropertyGptTemplate.txt');
        $templateString = file_get_contents($template);

        return '
            Please provide property information from the following message:' . "\n" .
            $message . "\n\n" .
            'with the following format:' . "\n\n" .
            $templateString. "\n\n" .
            'Your parser should be robust enough to handle variations in formatting and wording commonly found in such messages.'. "\n\n" .
            'Messages can contain more than one property informations.' . "\n\n" .
            'For multiple properties use numbers or ----- or === as separator in messages.' . "\n\n" .
            'Each properties has own title and description.' . "\n\n" .
            'Give me the json only.
        ';
    }

    /**
     * @param string $message
     * @return array<array<mixed>>
     */
    public function extractListingFromMessage($message): array
    {
        $answer = $this->chatGptService->seekAnswerWithRetry(Extractor::generatePrompt($message));

        Log::debug("Answer from LLM = " . $answer);

        $extractedData = json_decode(
            $answer,
            // 'false' means don't translate JSON object to associative array, because below we want to check whether
            // we get an object or an array of objects.
            false,
        );

        // Sometimes LLM returns a single object instead of array of objects, in that case wrap it in an array
        // because we want to process the answer as array of multiple listings below.
        if (!is_array($extractedData)) {
            $extractedData = [$extractedData];
        }

        return $extractedData;
    }
}
