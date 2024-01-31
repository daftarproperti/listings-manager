<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class TelegramInteractionHelper
{
    public static function sendMessage($chatId, $message,)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', config('services.telegram.bot_token'));

        $sendRequest = Http::get($url, $params);

        return $sendRequest->json();
    }
}
