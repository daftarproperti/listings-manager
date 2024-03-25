<?php

namespace App\Http\Controllers\Api;

use App\DTO\Telegram\Update;
use App\Helpers\Extractor;
use App\Helpers\Queue;
use App\Helpers\TelegramInteractionHelper;
use App\Http\Controllers\Controller;
use App\Http\Services\ReceiveMessageService;
use App\Jobs\ParseListingJob;
use App\Models\ListingUser;
use App\Models\RawMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receiveTelegramMessage(
        Request $request,
        ReceiveMessageService $receiveMessageService,
    ): JsonResponse {
        $params = $request->validate([
            'update_id' => 'required',
            'message' => 'nullable',
            'callback_query' => 'nullable',
        ]);

        $update = Update::from($params);

        //to avoid same message processing
        $dataExists = RawMessage::where('update_id', $update->update_id)->exists();
        if ($dataExists) {
            Log::warning("Update id {$update->update_id} already exists, ignoring.");
            return response()->json(['success' => true], 200);
        }

        //when message contain photo use 'caption' as message, because 'text' is not available.
        $message = !empty($update->message->caption) ? $update->message->caption : ($update->message->text ?? '');

        //to do next: use AI to check message is about property informations or not.
        $isPropertyInformationMessage = $receiveMessageService
            ->isPropertyInformationMessage(
                $message,
                10
            );

        if ($isPropertyInformationMessage) {
            $message = $receiveMessageService->saveRawMessage($update);

            $pictureUrls = [];
            if (!empty($update->message->photo)) {
                $pictureUrls = $receiveMessageService->pictureUrls($update->message->photo);
            }

            $chatId = isset($update->message->chat) ? $update->message->chat->id : null;

            ParseListingJob::dispatch(
                $update->message->text,
                $pictureUrls,
                $this->populateListingUser($update),
                $chatId
            )->onQueue(Queue::getQueueName('generic'));

            if ($chatId) {
                TelegramInteractionHelper::sendMessage(
                    $chatId,
                    'Terima kasih atas Listing yang anda bagikan.' . "\n\n" .
                        'Informasi sedang kami proses dan masukkan ke database...'
                );
            }
        } else {
            Log::info('is not property informations: ' . print_r($update, TRUE));
        }

        return response()->json(['success' => true], 200);
    }

    private function populateListingUser(Update $update): ListingUser
    {
        $listingUser = new ListingUser();
        $listingUser->name = trim(sprintf(
            '%s %s',
            $update->message->from->first_name,
            $update->message->from->last_name ?? ''
        ));
        $listingUser->userName = $update->message->from->username ?? null;
        $listingUser->userId = $update->message->from->id;
        $listingUser->source = 'telegram';

        return $listingUser;
    }
}
