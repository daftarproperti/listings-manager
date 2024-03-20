<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\Extractor;
use App\Helpers\TelegramInteractionHelper;
use App\Http\Services\ChatGptService;
use App\Models\Listing;
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
    public function handle(Extractor $extractor): void
    {
        try {
            Log::debug("Handling parse listing, message =\n" . $this->message);

            $extractedData = $extractor->extractListingFromMessage($this->message);

            foreach ($extractedData as $data) {
                $this->saveAnswer((array) $data, $this->user ?? null);
            }
        } catch (\Throwable $th) {
            if (!empty($this->chatId)) {
                TelegramInteractionHelper::sendMessage($this->chatId, 'Mohon maaf terjadi kesalahan pemrosesan informasi. Silahkan coba kembali.');
            }

            Log::error("Error caught when trying to extract listing data:");
            Log::error($th);
            return;
        }

        if (!empty($this->chatId)) {
            TelegramInteractionHelper::sendMessage($this->chatId, 'Informasi telah selesai kami proses.');
        }
    }

    /**
     * @param array<mixed> $data
     */
    public function saveAnswer(array $data, ListingUser $user = null): ?Listing
    {
        $listing = new Listing();

        foreach ($data as $key => $value) {
            $listing->$key = $value;
        }

        if ($user) {
            $listing->user = $user;
        }

        //set default to public view
        $listing->isPrivate = false;

        $listing->save();

        return $listing;
    }
}
