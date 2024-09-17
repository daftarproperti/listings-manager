<?php

namespace App\Http\Middleware;

use App\DTO\Telegram\Update;
use App\Models\Enums\ChatType;
use Closure;
use Illuminate\Http\Request;

class TelegramWebhook
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $secretToken = $request->route('secret_token');
        if (!$secretToken || $secretToken !== config('services.telegram.webhook_access_secret')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $requestDataMessage = Update::from($request->only('message'));

        $requestMessage = $requestDataMessage->message ?? null;
        $chat = $requestMessage->chat ?? null;
        $chatType = $chat->type ?? ChatType::PRIVATE->value;

        if ($chatType === ChatType::GROUP->value && $requestMessage) {
            return response()->json(['error' => 'Group not allowed.'], 200);
        }


        return $next($request);
    }
}
