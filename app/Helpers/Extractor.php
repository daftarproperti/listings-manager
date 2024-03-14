<?php

namespace App\Helpers;

use App\Http\Services\ChatGptService;

class Extractor
{
    /**
     * @param string $message
     */
    public static function generatePrompt($message): string
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
     */
    public static function extractListingFromMessage($message): mixed
    {
        $chatGptService = app(ChatGptService::class);

        $answer = $chatGptService->seekAnswer(Extractor::generatePrompt($message));

        return json_decode($answer, true);
    }
}
