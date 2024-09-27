<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Helpers\Extractor;
use App\Models\Coordinate;
use App\Models\GeneratedListing;
use App\Models\Resources\ListingResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateListingFromText implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $jobId;
    protected string $text;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $jobId, string $text)
    {
        $this->jobId = $jobId;
        $this->text = $text;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Extractor $extractor)
    {
        $extractedListing = $extractor->extractSingleListingFromMessage($this->text);

        $coordinate = new Coordinate();
        $coordinate->latitude = 0;
        $coordinate->longitude = 0;

        $extractedListing->id = 'temp-id';
        $extractedListing->listingId = 1234;
        $extractedListing->sourceText = $this->text;
        $extractedListing->coordinate = $coordinate;
        $extractedListing->contact = [
            'name' => $extractedListing->contact->name,
            'company' => $extractedListing->contact->company,
        ];
        $extractedListing->updated_at = Carbon::now();
        $extractedListing->user_profile = null;
        $extractedListing->adminNote = null;
        $extractedListing->cancellationNote = null;
        $extractedListing->closings = null;

        $listing = new ListingResource($extractedListing);
        Log::info('Broadcasting GenerateListingsFromTextResult', ['result' => $listing->toJson()]);

        GeneratedListing::create([
            'job_id' => $this->jobId,
            'generated_listing' => $listing,
        ]);
    }
}
