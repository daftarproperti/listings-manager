<?php

namespace App\Jobs;

use App\Helpers\Queue;
use App\Helpers\TelegramPhoto;
use App\Http\Services\GoogleStorageService;
use App\Models\Listing;
use App\Models\Resources\PublicListingResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncListingToGCS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $listingId)
    {
    }

    /**
     * Uploads the given listing to GCS to synchronize its data.
     */
    public function handle(): void
    {
        /** @var Listing|null $listing */
        $listing = Listing::where('listingId', $this->listingId)->first();
        if (!$listing) {
            logger()->error("Cannot find listing $this->listingId in database, not syncing to GCS.");
            return;
        }

        $resource = new PublicListingResource($listing);
        $json = type(json_encode($resource))->asString();
        logger()->debug("syncing listing " . $json);

        $googleStorageService = new GoogleStorageService();
        $updatedAt = $listing->updated_at->toIso8601ZuluString();
        $fileName = "listings/{$this->listingId}/{$this->listingId}-$updatedAt.json";
        logger()->debug("writing to file $fileName");
        $googleStorageService->uploadFile($json, $fileName);

        logger()->debug("Successfully uploading to GCS for listing $this->listingId");

        if (env('ETH_LIVE_PUSH')) {
            Web3AddListing::dispatch(
                $this->listingId,
                $listing->cityName ?? 'Default City',
                TelegramPhoto::getGcsUrlFromFileName($fileName),
            )->onQueue(Queue::getQueueName('generic'));
        }
    }
}
