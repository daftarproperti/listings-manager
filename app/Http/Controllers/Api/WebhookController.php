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

            //to do next: use AI to check message is about property informations or not.
            $isPropertyInformationMessage = $receiveMessageService->isPropertyInformationMessage($params['message']['text'] ?? '');

            if ($isPropertyInformationMessage) {
                $receiveMessageService->saveRawMessage($params);

                $template = storage_path('HousePropertyGptTemplate.txt');
                $templateString = file_get_contents($template);

                $queueService->queueGptProcess(
                    'Please give me json only '."\n".
                    $params['message']['text']."\n".'with following format:'."\n".$templateString
                );
            }

        } catch (\Throwable $e) {
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
