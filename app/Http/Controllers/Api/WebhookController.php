<?php

namespace App\Http\Controllers\Api;

use App\DTO\Telegram\Update;
use App\Http\Controllers\Controller;
use App\Http\Services\ParseService;
use App\Http\Services\ReceiveMessageService;
use App\Models\Enums\MessageClassification;
use App\Models\RawMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receiveTelegramMessage(
        Request $request,
        ReceiveMessageService $receiveMessageService,
        ParseService $parseService
    ): JsonResponse {
        $params = $request->validate([
            'update_id' => 'required',
            'message' => 'nullable',
            'callback_query' => 'nullable',
        ]);

        $update = Update::from($params);

        // stop process if message not exists
        if (is_null($update->message)) {
            return response()->json(['error' => 'no message to process'], 200);
        }

        //to avoid same message processing
        $dataExists = RawMessage::where('update_id', $update->update_id)->exists();
        if ($dataExists) {
            Log::warning("Update id {$update->update_id} already exists, ignoring.");
            return response()->json(['success' => true], 200);
        }

        //when message contain photo use 'caption' as message, because 'text' is not available.
        $message = !empty($update->message->caption) ? $update->message->caption : ($update->message->text ?? '');

        //to do next: use AI to check message is about property informations or not.
        $classification = $receiveMessageService
            ->classifyMessage(
                $message,
                10
            );

        if ($classification == MessageClassification::LISTING->value) {

            $parseService->parseListing($update);

        } else if ($classification == MessageClassification::BUYER_REQUEST->value) {

            $parseService->parseBuyerRequest($update);

        } else {
            Log::info('is not property / buyer request informations: ' . print_r($update, TRUE));
        }

        return response()->json(['success' => true], 200);
    }
}
