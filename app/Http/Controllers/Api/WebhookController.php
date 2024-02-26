<?php

namespace App\Http\Controllers\Api;

use App\DTO\Telegram\Update;
use App\Helpers\TelegramInteractionHelper;
use App\Http\Controllers\Controller;
use App\Http\Services\ReceiveMessageService;
use App\Jobs\ParseListingJob;
use App\Models\ListingUser;
use App\Models\PropertyUser;
use App\Models\RawMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Serializer\Serializer;

class WebhookController extends Controller
{
    private Serializer $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function receiveTelegramMessage(
        Request $request,
        ReceiveMessageService $receiveMessageService,
    ): JsonResponse {
        try {
            $params = $request->validate([
                'update_id' => 'required',
                'message' => 'nullable',
                'callback_query' => 'nullable',
            ]);

            /** @var Update $update */
            $update = $this->serializer->denormalize($params, Update::class);

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

                $mainPrompt = sprintf(
                    '%s%s',
                    $update->message->text ?? '',
                    !empty($pictureUrls) ? "\n Picture Urls:\n" . implode("\n", $pictureUrls) . "\n" : ''
                );

                $listingUser = new ListingUser();
                $listingUser->name = trim(sprintf(
                    '%s %s',
                    $update->message->from->first_name,
                    $update->message->from->last_name ?? ''
                ));
                $listingUser->userName = $update->message->from->username ?? null;
                $listingUser->userId = $update->message->from->id;
                $listingUser->source = 'telegram';

                $chatId = isset($update->message->chat) ? $update->message->chat->id : null;

                ParseListingJob::dispatch(
                    'Please give me json only also trim the value' . "\n" .
                        $mainPrompt . "\n\n" . 'with following format:' . "\n\n" . $templateString,
                    $listingUser,
                    $chatId
                );

                if ($chatId) {
                    TelegramInteractionHelper::sendMessage($chatId, 'Terimakasih atas informasi yang diberikan.' . "\n" . 'Informasi sedang kami proses.');
                }
            } else {
                Log::info('is not property informations: ' . print_r($update, TRUE));
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->status ?? 500);
        }

        return response()->json(['success' => true], 200);
    }
}
