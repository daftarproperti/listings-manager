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
use App\Models\ListingUser;

class ParseListingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $message;
    private ListingUser $user;
    private ?int $chatId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, ListingUser $user, int $chatId = null)
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
        try {
            Log::debug("Handling parse listing, message =\n" . $this->message);

            $answer = $chatGptService->seekAnswer($this->message);

            $extractedData = (array) json_decode($answer, true);

            foreach ($extractedData as $data) {
                $chatGptService->saveAnswer((array) $data, $this->user ?? null);
            }

        } catch (\Throwable $th) {
            if (!empty($this->chatId)) {
                TelegramInteractionHelper::sendMessage($this->chatId, 'Mohon maaf terjadi kesalahan pemrosesan informasi. Silahkan coba kembali.');
            }

            Log::error($th->getMessage(), $th->getTrace());
        }

        if (!empty($this->chatId)) {
            TelegramInteractionHelper::sendMessage($this->chatId, 'Informasi telah selesai kami proses.');
        }
    }
}
