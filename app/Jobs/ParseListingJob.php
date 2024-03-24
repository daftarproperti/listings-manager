<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\Assert;
use App\Helpers\Extractor;
use App\Helpers\TelegramInteractionHelper;
use App\Http\Services\ChatGptService;
use App\Models\Listing;
use App\Models\ListingUser;

class ParseListingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $sourceText;
    /** @var array<string> $pictureUrls */
    private array $pictureUrls;
    private ListingUser $user;
    private ?int $chatId;

    /**
     * Create a new job instance.
     *
     * @param array<string> $pictureUrls
     */
    public function __construct(string $sourceText, array $pictureUrls, ListingUser $user, int $chatId = null)
    {
        $this->sourceText = $sourceText;
        $this->pictureUrls = $pictureUrls;
        $this->user = $user;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     */
    public function handle(ChatGptService $chatGptService): void
    {
        $extractor = new Extractor($chatGptService);

        try {
            Log::debug("Handling parse listing, source text =\n" . $this->sourceText);

            $extractedData = $extractor->extractListingFromMessage($this->sourceText);

            foreach ($extractedData as $data) {
                $this->saveAnswer((array) $data, $this->sourceText, $this->pictureUrls, $this->user ?? null);
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
            $titles = implode("\n", array_map(function($listing) {
                return isset($listing->title) ? '* <b>' . $listing->title . '</b>' : '';
            }, $extractedData));
            TelegramInteractionHelper::sendMessage(
                $this->chatId,
                'Listing telah terproses dan masuk ke database, ' .
                'sehingga dapat ditemukan di jaringan Daftar Properti:' . "\n" .
                $titles . "\n\n" .
                'Klik tombol "Kelola Listing" di bawah untuk meng-edit atau menambahkan foto sehingga lebih menarik bagi pencari.',
            );
        }
    }

    // Sanitize the fields that we get from LLM since LLM is not 100% correct.
    private function sanitizeField(string $key, mixed $value): mixed {
        switch ($key) {
        case "propertyType":
            // In case LLM doesn't understand that enum has to be lower case.
            return strtolower(Assert::castToString($value));
        default:
            return $value;
        }
    }

    /**
     * @param array<mixed> $data
     * @param array<string> $pictureUrls
     */
    public function saveAnswer(array $data, string $sourceText, array $pictureUrls, ListingUser $user = null): ?Listing
    {
        $listing = new Listing();

        foreach ($data as $key => $value) {
            $listing->$key = $this->sanitizeField($key, $value);
        }

        if ($user) {
            $listing->user = $user;
        }

        $listing->sourceText = $sourceText;
        $listing->pictureUrls = $pictureUrls;

        //set default to public view
        $listing->isPrivate = false;

        $listing->save();

        return $listing;
    }
}
