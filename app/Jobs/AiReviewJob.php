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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            $reviewPrompt = AiReviewPrompt::generatePrompt($listingReviewData);
            $response = $chatGptService->seekAnswer($reviewPrompt, 'gpt-4');

            Log::info('Ai Review Job dispatched: ', ['ai_review' => $response]);
            $jsonResponse = (array) json_decode($response, true);

            $this->listing->aiReview()->update([
                'results' => $jsonResponse['results'] ?? [],
                'status' => (AiReviewStatus::DONE)->value,
            ]);

        } catch (\Throwable $th) {
            Log::error('Ai Review Job error: ', ['error' => $th->getMessage()]);
            //Rollback aiReview status to processable state ("done")
            $this->listing->aiReview()->update(['status' => (AiReviewStatus::DONE)->value]);
        }
    }
}
