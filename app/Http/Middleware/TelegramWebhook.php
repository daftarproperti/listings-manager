<?php

namespace App\Http\Middleware;

use App\DTO\Telegram\Message;
use App\DTO\Telegram\Update;
use App\Helpers\Assert;
use App\Models\Enums\ChatType;
use App\Models\TelegramAllowlistGroup;
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

        $chatType = $requestDataMessage->message->chat->type;

        if ($chatType === ChatType::GROUP->value) {
            return $this->isAllowedGroup($requestDataMessage->message) ?
                $next($request) : response()->json(['error' => 'Unauthorized'], 403);
        }


        return $next($request);
    }

    private function isAllowedGroup(Message $message): bool
    {
        $allowlistGroup = TelegramAllowlistGroup::where('chatId', $message->chat->id)->first();

        if (!$allowlistGroup) {
            $allowlistGroup = TelegramAllowlistGroup::create([
                'chatId' => $message->chat->id,
                'allowed' => false,
                'sampleMessage' => $message->text,
            ]);
        }

        return Assert::boolean($allowlistGroup->allowed);
    }
}
