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

        $requestMessage = $requestDataMessage->message ?? null;
        $chat = $requestMessage->chat ?? null;
        $chatType = $chat->type ?? ChatType::PRIVATE->value;

        if ($chatType === ChatType::GROUP->value && $requestMessage) {
            return $this->isAllowedGroup($requestMessage) ?
                $next($request) : response()->json(['error' => 'Group not allowed.'], 200);
        }


        return $next($request);
    }

    private function isAllowedGroup(Message $message): bool
    {
        /** @var TelegramAllowlistGroup|null $allowlistGroup */
        $allowlistGroup = TelegramAllowlistGroup::where('chatId', $message->chat->id)->first();

        if (!$allowlistGroup) {
            /** @var TelegramAllowlistGroup $allowlistGroup */
            $allowlistGroup = TelegramAllowlistGroup::create([
                'chatId' => $message->chat->id,
                'allowed' => false,
                'sampleMessage' => $message->text,
            ]);
        }

        return Assert::boolean($allowlistGroup->allowed);
    }
}
