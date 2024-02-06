<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TelegramInteractionHelper;
use App\Http\Controllers\Controller;
use App\Http\Services\ChatGptService;
use App\Http\Services\QueueService;
use App\Http\Services\ReceiveMessageService;
use App\Models\PropertyUser;
use App\Models\RawMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receiveTelegramMessage(
        Request $request,
        ReceiveMessageService $receiveMessageService,
        QueueService $queueService
    ) {
        try {
            $params = $request->validate([
                'update_id' => 'required',
                'message' => 'nullable',
                'callback_query' => 'nullable',
            ]);

            //to avoid same message processing
            $dataExists = RawMessage::where('update_id', (int) $params['update_id'])->exists();
            if ($dataExists) {
                return response()->json(['success' => true], 200);
            }

            //when message contain photo use 'caption' as message, because 'text' is not available.
            $message = !empty($params['message']['caption']) ? $params['message']['caption'] : ($params['message']['text'] ?? '');

            //to do next: use AI to check message is about property informations or not.
            $isPropertyInformationMessage = $receiveMessageService
                ->isPropertyInformationMessage(
                    $message,
                    10
                );

            if ($isPropertyInformationMessage) {
                $message = $receiveMessageService->saveRawMessage($params);

                $pictureUrls = [];
                if (!empty($params['message']['photo'])) {
                    $pictureUrls = $receiveMessageService->pictureUrls($params['message']['photo']);
                }

                $template = storage_path('HousePropertyGptTemplate.txt');
                $templateString = file_get_contents($template);

                $mainPrompt = sprintf(
                    '%s%s',
                    $params['message']['text'] ?? '',
                    !empty($pictureUrls) ? "\n Picture Urls:\n" . implode("\n", $pictureUrls) . "\n" : ''
                );

                $propertyUser = new PropertyUser();
                $propertyUser->name = trim(sprintf('%s %s', $params['message']['from']['first_name'], $params['message']['from']['last_name'] ?? ''));
                $propertyUser->userName = $params['message']['from']['username'] ?? null;
                $propertyUser->userId = $params['message']['from']['id'];
                $propertyUser->source = 'telegram';

                $chatId = isset($params['message']['chat']) ? $params['message']['chat']['id'] : null;

                $queueService->queueGptProcess(
                    'Please give me json only also trim the value'."\n".
                    $mainPrompt."\n\n".'with following format:'."\n\n".$templateString,
                    $propertyUser,
                    $chatId
                );

                if ($chatId) {
                    TelegramInteractionHelper::sendMessage($chatId, 'Terimakasih atas informasi yang diberikan.'."\n".'Informasi sedang kami proses.');
                }

            } else {
                Log::info('is not property informations', $params);
            }

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->status ?? 500);
        }

        return response()->json(['success' => true], 200);
    }

    public function processGpt(Request $request, ChatGptService $chatGptService) {

        try {
            $params = $request->validate([
                'message' => 'required',
                'user' => 'nullable',
                'chat_id' => 'nullable'
            ]);

            $answer = $chatGptService->seekAnswer($params['message']);

            //avoid insert empty informations
            $extractedData = json_decode($answer);

            if (!$extractedData->title || !$extractedData->description) {
                TelegramInteractionHelper::sendMessage($params['chat_id'], 'Mohon maaf terjadi kesalahan pemrosesan informasi. Silahkan coba kembali.');
                return response()->json(['answer' => $answer], 200);
            }

            $chatGptService->saveAnswer($extractedData, $params['user'] ?? null);

            if (!empty($params['chat_id'])) {
                TelegramInteractionHelper::sendMessage($params['chat_id'], 'Informasi telah selesai kami proses.');
            }

            return response()->json(['answer' => json_decode($answer)], 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->status ?? 500);
        }
    }
}
