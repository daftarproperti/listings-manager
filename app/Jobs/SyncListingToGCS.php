<?php

namespace App\Jobs;

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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        logger()->debug('syncing listing ' . $json);

        $googleStorageService = new GoogleStorageService();
        $fileName = Web3Listing::getOffChainFileName($listing);
        logger()->debug("writing to file $fileName");
        $googleStorageService->uploadFile($json, $fileName);

        logger()->debug("Successfully uploading to GCS for listing $this->listingId");
    }
}
