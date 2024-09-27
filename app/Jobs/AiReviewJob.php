<?php

namespace App\Jobs;

use App\Helpers\AiReviewPrompt;
use App\Http\Services\ChatGptService;
use App\Models\Enums\AiReviewStatus;
use App\Models\Listing;
use App\Models\Resources\AiReviewListingGptResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AiReviewJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Listing $listing;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Listing $listing)
    {
        $this->listing = $listing;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChatGptService $chatGptService)
    {
        try {
            $listingReviewData = (new AiReviewListingGptResource($this->listing))->resolve();
            $reviewPrompt = AiReviewPrompt::generatePrompt($listingReviewData, $this->listing->description);

            $promptMessage[] = [
                'role' => 'user',
                'content' => $reviewPrompt,
            ];

            $firstPromptResponse = $chatGptService->seekAnswerWihtCustomMessagesRole($promptMessage, 'gpt-4');

            // Keep context from the first response
            $promptMessage[] = [
                'role' => 'assistant',
                'content' => $firstPromptResponse,
            ];

            $validationPrompt = AiReviewPrompt::validationPrompt();
            $promptMessage[] = [
                'role' => 'user',
                'content' => $validationPrompt
            ];

            $finalResponse = $chatGptService->seekAnswerWihtCustomMessagesRole($promptMessage, 'gpt-4');

            /** @var array<array<string>> $finalJsonResponse */
            $finalJsonResponse = json_decode($finalResponse, true);

            $this->listing->aiReview()->update([
                'results' => $finalJsonResponse['results'] ?? [],
                'status' => (AiReviewStatus::DONE)->value,
            ]);
        } catch (\Throwable $th) {
            Log::error('Ai Review Job error: ', ['error' => $th->getMessage()]);
            //Rollback aiReview status to processable state ("done")
            $this->listing->aiReview()->update(['status' => (AiReviewStatus::DONE)->value]);
        }
    }
}
