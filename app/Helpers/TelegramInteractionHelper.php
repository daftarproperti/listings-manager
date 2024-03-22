<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class TelegramInteractionHelper
{
    public static function sendMessage(int $chatId, string $message): mixed
    {
        // Use html to format text: https://core.telegram.org/bots/api#formatting-options
        $params = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'html',
        ];

        $url = sprintf(
            'https://api.telegram.org/bot%s/sendMessage',
            Assert::string(config('services.telegram.bot_token'))
        );

        $sendRequest = Http::get($url, $params);

        return $sendRequest->json();
    }
}
