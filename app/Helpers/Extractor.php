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

        return <<<EOD
I am going to give you a real estate advertisement text in Indonesia (also in Bahasa Indonesia).
I will need to extract the unstructured information into structured fields, which I will tell you the detail below.

--- BEGIN REAL ESTATE ADVERTISEMENT TEXT IN INDONESIAN ---
$message
--- END REAL ESTATE ADVERTISEMENT TEXT ---

Here are the fields that you need to extract:
--- BEGIN FIELDS IN JSON ---
$templateString
--- END FIELDS ---

Your extraction should be robust enough to handle variations in formatting and wording commonly found in such messages,
because the ad text is written using natural language in Bahasa Indonesia.

Note that the text can contain more than one property ads. For example, it may be numbered, separated by line-like
text, or just by newlines. Use your natural language judgement.

The output that I want is JSON array, with each element being a JSON object following the template fields I gave you
above. I will directly feed your output into a program, so please reply directly in JSON without any message for human.
EOD;
    }

    /**
     * @param string $message
     * @return array<object>
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
