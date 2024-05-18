<?php

namespace App\Helpers;

use App\Http\Services\ChatGptService;
use Illuminate\Support\Facades\Log;

class Extractor
{
    private ChatGptService $chatGptService;

    public function __construct(ChatGptService $chatGptService)
    {
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
text, or just by newlines. As for the contacts it will be the same across property ads.
Use your natural language judgement.

The output that I want is JSON array, with each element being a JSON object following the template fields I gave you
above. I will directly feed your output into a program, so please reply directly in JSON without any message for human.
EOD;
    }

    public function generatePromptToSplit(string $message): string
    {
        return <<<EOD
At the end of this prompt, I will give you a real estate listing text in Indonesia. The text may contain a single
listing or multiple listings in Bahasa Indonesia.

I need your help to transform this text into separate listing texts for each listing.

Here are 2 important rules to remember:
1. If the text contains multiple listings, reply with array of texts, each is a single listing copied verbatim from the
   source text that contains multiple listings. Also reply with the global header and the global footer from the text.
   This reply should be formatted in JSON and I will detail the format below.
2. If the text contains only one real estate property being advertised, do not reply with json but a special response
   string "SINGLE_LISTING".

Usually a text containing multiple listings are formatted like this (not a strict format, so use generic natural
language judgement too):

------ BEGIN SAMPLE FORMAT -----
<header> (optional, usually tagline like "Dijual rumah di Jakarta")

<listing 1>

<listing 2>

<listing 3>

(each of the listings above may be numbered or separated by empty lines, use your natural language judgement)

<footer> (optional, usually contains contact information)
------ END SAMPLE FORMAT -----

If the text contains multiple listings, here is the json format for your return:
{
    "header": "<header>",
    "footer": "<footer>",
    "listings": [
      "<listing 1>",
      "<listing 2>",
      "<listing 3>",
      ...
    ]
}

If the text contains just one property being advertised, this is a special case that I want to handle, so reply with
text "SINGLE_LISTING" and do not return json.

I will feed your response directly into a program, so make sure to return a proper JSON or "SINGLE_LISTING" without
any additional text.

Now, here is the listing text that you need to process:
----------- BEGIN LISTING TEXT -------------
$message
----------- END LISTING TEXT -------------

EOD;
    }

    public function generateBuyerRequestPrompt(string $message): string
    {
        $template = storage_path('BuyerRequestGptTemplate.txt');
        $templateString = file_get_contents($template);

        return <<<EOD
I will give you unstructured message about a real estate property buyer request.
The message can be in indonesian or in english or any language.
I need your help to extract the unstructured information into structured fields, which I will tell you the detail below.

--- BEGIN REAL ESTATE BUYER REQUEST TEXT ---
$message
--- END REAL ESTATE BUYER REQUEST TEXT

Here are the fields that you need to extract:
--- BEGIN FIELDS IN JSON ---
$templateString
--- END FIELDS ---

Your extraction should be robust enough to handle variations in formatting and wording commonly found in such messages,
because the buyer request text is written using natural language in Bahasa Indonesia.

I will feed your response directly into a program, so make sure to return a proper JSON without any additional text.
EOD;
    }

    /**
     * @return array<string>
     */
    private function splitMessages(string $message): array
    {
        // Force model to GPT-4 for splitting messages since GPT 3.5 is not very good at handling long text and
        // understanding the separation of multiple listings.
        // But once split, GPT-3.5 performs well enough for extraction.
        $answer = $this->chatGptService->seekAnswer(Extractor::generatePromptToSplit($message), 'gpt-4');

        Log::debug("answer from split = " . $answer);

        if (str_contains($answer, 'SINGLE_LISTING')) {
            return [$message];
        }

        /** @var object{header: string, footer: string, listings: array<string>} $ret */
        $ret = json_decode($answer);

        if (isset($ret->listings) && count($ret->listings) == 1) {
            // LLM sometimes doesn't get it right, it may return array of listings rather than the special
            // "SINGLE_LISTING" string. So if the array is only one length, we would rather process this specially
            // and not use the split return from LLM.
            Log::debug("Split only contains 1 listing, use original message instead of split return from LLM");
            return [$message];
        }

        $header = $ret->header ?? '';
        $footer = $ret->footer ?? '';

        return array_map(function ($listing) use ($header, $footer) {
            return "$header\n\n$listing\n\n$footer";
        }, isset($ret->listings) ? $ret->listings : []);
    }

    /**
     * @param string $message
     * @return array<object>
     */
    public function extractListingFromMessage($message): array
    {
        $splitMessages = $this->splitMessages($message);

        $extractedData = [];

        foreach ($splitMessages as $key => $singleMsg) {
            Log::debug("extracting listing #$key, msg = $singleMsg");

            $answer = $this->chatGptService->seekAnswerWithRetry(Extractor::generatePrompt($singleMsg));

            Log::debug("Answer from LLM = " . $answer);

            $extracted = json_decode(
                $answer,
                // 'false' means don't translate JSON object to associative array, because below we want to check whether
                // we get an object or an array of objects.
                false,
            );

            if (!$extracted) {
                Log::error("Failed to parse JSON from LLM");
            }

            // Sometimes LLM returns a single object instead of array of objects, in that case wrap it in an array
            // because we want to process the answer as array of multiple listings below.
            if (!is_array($extracted)) {
                $extracted = [$extracted];
            }

            foreach ($extracted as $singleExtracted) {
                $singleExtracted->description = $singleMsg;
            }

            $extractedData = array_merge($extractedData, $extracted);
        }

        return $extractedData;
    }

    /**
     * @param string $message
     * @return array<object>
     */
    public function extractBuyerRequestFromMessage($message): array
    {
        Log::debug("extracting buyer request , msg = $message");
        $answer = $this->chatGptService->seekAnswerWithRetry(Extractor::generateBuyerRequestPrompt($message));
        Log::debug("Answer from LLM (BUYER REQUEST) = " . $answer);

        $extracted = json_decode($answer, false);

        if (!is_array($extracted)) {
            $extracted = [$extracted];
        }

        return $extracted;
    }
}
