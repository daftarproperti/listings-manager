<?php

namespace App\Http\Controllers\Api;

use App\DTO\Telegram\Update;
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

            $template = storage_path('HousePropertyGptTemplate.txt');
            $templateString = file_get_contents($template);

            $baseMessage = sprintf(
                '%s%s',
                $update->message->text ?? '',
                !empty($pictureUrls) ? "\n Picture Urls:\n" . implode("\n", $pictureUrls) . "\n" : ''
            );

            $promptMessage = '
                Please provide property information from the following message:' . "\n" .
                $baseMessage . "\n\n" .
                'with the following format:' . "\n\n" .
                $templateString. "\n\n" .
                'Your parser should be robust enough to handle variations in formatting and wording commonly found in such messages.'. "\n\n" .
                'Messages can contain more than one property informations.' . "\n\n" .
                'For multiple properties use numbers or ----- or === as separator in messages.' . "\n\n" .
                'Each properties has own title and description.' . "\n\n" .
                'Give me the json only.
            ';

            $chatId = isset($update->message->chat) ? $update->message->chat->id : null;

            ParseListingJob::dispatch(
                $promptMessage,
                $this->populateListingUser($update),
                $chatId
            );

            if ($chatId) {
                TelegramInteractionHelper::sendMessage($chatId, 'Terimakasih atas informasi yang diberikan.' . "\n" . 'Informasi sedang kami proses.');
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
