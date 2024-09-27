<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\Extractor;
use App\Http\Services\ChatGptService;
use App\Models\ListingUser;
use App\Models\SavedSearch;

class ParseBuyerRequestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?string $sourceText;
    private ?ListingUser $user;

    /**
     * Create a new job instance.
     */
    public function __construct(
        ?string $sourceText,
        ?ListingUser $user
    ) {
        $this->sourceText = $sourceText;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(ChatGptService $chatGptService): void
    {
        $extractor = new Extractor($chatGptService);

        try {
            Log::debug("Handling parse buyer request, source text =\n" . $this->sourceText);

            if (!$this->sourceText) {
                throw new \Exception('Empty source text');
            }

            $extractedData = $extractor->extractBuyerRequestFromMessage($this->sourceText);

            if (!$extractedData) {
                throw new \Exception('Empty extracted data');
            }

            if ($this->user) {
                foreach ($extractedData as $data) {
                    $this->saveAnswer((array) $data, $this->user);
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error caught when trying to extract buyer request data:');
            Log::error($th);
            return;
        }
    }

    /**
     * @param array<mixed> $data
     */
    public function saveAnswer(array $data, ListingUser $user): ?SavedSearch
    {
        $savedSearch = new SavedSearch();

        foreach ($data as $key => $value) {
            $savedSearch->{$key} = $value;
        };

        $savedSearch->userId = $user->userId;
        $savedSearch->save();

        return $savedSearch;
    }
}
