<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\ChatGptService;
use App\Http\Services\QueueService;
use App\Http\Services\ReceiveMessageService;
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

                $isPropertyInformationMessage = true;

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
                    $message,
                    !empty($pictureUrls) ? "\n Picture Urls:\n" . implode("\n", $pictureUrls) . "\n" : ''
                );

                $queueService->queueGptProcess(
                    'Please give me json only also trim the value'."\n".
                    $mainPrompt."\n".'with following format:'."\n".$templateString
                );
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
            ]);

            $answer = $chatGptService->seekAnswer($params['message']);
            $chatGptService->saveAnswer(json_decode($answer));

            return response()->json(['answer' => json_decode($answer)], 200);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], $e->status ?? 500);
        }
    }
}
