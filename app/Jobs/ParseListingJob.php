<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\TelegramInteractionHelper;
use App\Http\Services\ChatGptService;
use App\Models\PropertyUser;

class ParseListingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $message;
    private PropertyUser $user;
    private ?int $chatId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, PropertyUser $user, int $chatId = null)
    {
        $this->message = $message;
        $this->user = $user;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     */
    public function handle(ChatGptService $chatGptService): void
    {
        Log::debug("Handling parse listing, message =\n" . $this->message);

        $answer = $chatGptService->seekAnswer($this->message);

        //avoid insert empty informations
        /** @var array<string> $extractedData */
        $extractedData = json_decode($answer, true);

        if ($this->chatId && (!$extractedData['title'] || !$extractedData['description'])) {
            TelegramInteractionHelper::sendMessage($this->chatId, 'Mohon maaf terjadi kesalahan pemrosesan informasi. Silahkan coba kembali.');
            return;
        }

        $chatGptService->saveAnswer($extractedData, $this->user ?? null);

        if (!empty($this->chatId)) {
            TelegramInteractionHelper::sendMessage($this->chatId, 'Informasi telah selesai kami proses.');
        }
    }
}
