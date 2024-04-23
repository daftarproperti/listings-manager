<?php

namespace App\Http\Controllers\Api;

use App\DTO\Telegram\Message;
use App\DTO\Telegram\Update;
use App\Helpers\Extractor;
use App\Helpers\Queue;
use App\Helpers\TelegramInteractionHelper;
use App\Http\Controllers\Controller;
use App\Http\Services\ReceiveMessageService;
use App\Jobs\ParseListingJob;
use App\Models\ListingUser;
use App\Models\TelegramUser;
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
        $isPropertyInformationMessage = $receiveMessageService
            ->isPropertyInformationMessage(
                $message,
                10
            );

        if ($isPropertyInformationMessage) {
            $rawMessage = $receiveMessageService->saveRawMessage($update);

            $pictureUrls = [];
            if (!empty($update->message->photo)) {
                $pictureUrls = $receiveMessageService->pictureUrls($update->message->photo);
            }

            $chatId = isset($update->message->chat) ? $update->message->chat->id : null;
            $listingUser = $this->populateListingUser($update->message);

            $emptyProfile = false;
            $telegramUser = TelegramUser::where('user_id', $listingUser->userId)->first();
            if ($telegramUser) {
                $userProfile = $telegramUser->profile;
                if (!$userProfile || !property_exists($telegramUser, 'profile')) {
                    $emptyProfile = true;
                }
            }

            ParseListingJob::dispatch(
                $update->message->text,
                $pictureUrls,
                $listingUser,
                $chatId,
                $rawMessage
            )->onQueue(Queue::getQueueName('generic'));

            $isPrivateChat = isset($update->message->chat) && $update->message->chat->type == 'private';

            if ($chatId && $isPrivateChat) {
                $message = 'Terima kasih atas Listing yang anda bagikan.' . "\n\n" . 'Informasi sedang kami proses dan masukkan ke database...';
                if ($emptyProfile) {
                    $message = $message . "\n\n" . 'Agar listing lebih dapat ditemukan pencari, silahkan lengkapi data diri anda melalui Kelola Listing -> Akun';
                }

                TelegramInteractionHelper::sendMessage(
                    $chatId,
                    $message
                );
            }
        } else {
            Log::info('is not property informations: ' . print_r($update, TRUE));
        }

        return response()->json(['success' => true], 200);
    }

    private function populateListingUser(Message $message): ListingUser
    {
        $listingUser = new ListingUser();
        $listingUser->name = trim(sprintf(
            '%s %s',
            $message->from->first_name ?? '',
            $message->from->last_name ?? ''
        ));
        $listingUser->userName = $message->from->username ?? null;
        $listingUser->userId = $message->from->id ?? 0;
        $listingUser->source = 'telegram';

        return $listingUser;
    }
}
