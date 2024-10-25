<?php

namespace App\Jobs;

use App\Helpers\Cast;
use App\Helpers\Extractor;
use App\Models\Enums\AiReviewStatus;
use App\Models\Listing;
use App\Models\Resources\ListingResource;
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

    public function __construct(Listing $listing)
    {
        $this->listing = $listing;
    }

    /**
     * @param array<string, mixed> $extracted
     * @param array<string, mixed> $original
     *
     * @return array<string>
     */
    private static function listingDiff(array $extracted, array $original): array
    {
        $mismatches = [];

        $checkedFields = [
            'propertyType',
            'bedroomCount',
            'additionalBedroomCount',
            'bathroomCount',
            'additionalBathroomCount',
            'floorCount',
            'electricPower',
            'facing',
            'ownership',
        ];

        foreach ($extracted as $field => $val) {
            if (!in_array($field, $checkedFields)) {
                continue;
            }

            if (isset($val) && isset($original[$field])) {
                $extractedField = Cast::toString($val);
                $originalField = Cast::toString($original[$field]);
                if ($extractedField != $originalField) {
                    $mismatches[] = "Field $field tidak cocok: data = $originalField vs deskripsi = $extractedField";
                }
            }
        }

        return $mismatches;
    }

    /**
     * Runs automated review of a listing.
     *
     * Currently this job implements reviewing description vs fields accuracy only.
     * Eventually we should be able to automate all the cases in https://daftarproperti.org/checklist.
     *
     * @return void
     */
    public function handle(Extractor $extractor)
    {
        try {
            $extractedListing = $extractor->extractSingleListingFromMessage($this->listing->description, 'gpt-4');

            // TODO: No need to convert to JSON back and forth, but the extraction from LLM can be directly array.
            /** @var array<string, mixed> $extracted */
            $extracted = json_decode(type(json_encode($extractedListing))->asString(), true);

            $results = self::listingDiff($extracted, (new ListingResource($this->listing))->resolve());
            // TODO: Add results from field validations as well.
            // Looks like field validations need to be prompted one per field to achieve good accuracy.
            $this->listing->aiReview()->update([
                'results' => $results,
                'status' => (AiReviewStatus::DONE)->value,
            ]);
        } catch (\Throwable $th) {
            Log::error('Ai Review Job error: ', ['error' => $th->getMessage()]);
            //Rollback aiReview status to processable state ("done")
            $this->listing->aiReview()->update(['status' => (AiReviewStatus::DONE)->value]);
        }
    }
}
