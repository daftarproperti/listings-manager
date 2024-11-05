<?php

namespace App\Jobs;

use App\Helpers\Cast;
use App\Helpers\Extractor;
use App\Http\Services\ChatGptService;
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
     * @return array<string> Messages to be displayed to user
     */
    private function checkAddressFormat(ChatGptService $chatGptService, string $address): array
    {
        $prompt = <<<EOD
I am going to give you an address in Indonesia, please fix the address to follow standard format according to these
rules:

* Most address start with "Jl.", which means Jalan (Street).
  Some non-standard format is like "Jalan" or "Jl" (without the period) or "jl." (wrong capitalization).
  If the address starts with these variations please fix it to standard.
* Some address may not start with "Jl." if this starts with the building name, e.g. Perumahan name or Apartment name.
  If this sounds like building name/place name/perumahan name, this should be considered not a mistake,
  but otherwise it should be considered a mistake of missing the "Jl." prefix.
* An address should follow standard title typography, which means space after punctuation, capitalize names, etc.
* If a roman numeral appears it should be all caps and no periods, e.g. fix iv to be IV
* The format of the address should be:
  [optional building name like Perumahan Name or Apartment Name]
  Jl. <street name, may contain roman numerals if there is street number at the end>
  <house/building number, may explicitly mention "No." before the house/building number>,
  [optional administrative districts separated by commas like "RT XX, RW XX, Kelurahan, Kecamatan, Kota", need to be in
  name capitalization].

I need you to output in JSON format like this:
{
  // explain what the mistakes in the address, use Indonesian language for the explanations
  errors: ['<explanation 1>', 'explanation 2', ...]
  fix: '<your suggested fix according to the rules above>'
}

If the address is already correct, set both `errors` and `fix` as null.

Here is the address to check:
$address
EOD;
        $answer = $chatGptService->seekAnswer($prompt, 'gpt-4-turbo', 'json_object');
        $addressAnswer = json_decode($answer, true);
        if (is_array($addressAnswer) && isset($addressAnswer['errors'])) {
            $results = ['Format alamat: ' . implode(', ', $addressAnswer['errors'])];
            if (isset($addressAnswer['fix'])) {
                $results[] = 'Rekomendasi format alamat: ' . $addressAnswer['fix'];
            }
            return $results;
        } else {
            logger()->error('error decoding answer of LLM address check');
        }
        return [];
    }

    /**
     * @return array<string> Messages to be displayed to user
     */
    private function checkMultipleSpecs(ChatGptService $chatGptService, string $description): array
    {
        $prompt = <<<EOD
I am going to give you a real estate listing description in Indonesia. I want you to review whether this listing
contains a single set of spec or whether it contains multiple sets specs.

A listing with multiple sets of specs usually mention several types of the same property, maybe it's advertising
apartment with several different unit models. So detection may be based on:
* whether the listing mentions several different number of bedrooms, different size (luas bangunan)
* whether the listing explicitly mentions that several types are available (e.g. tersedia 2 model)

I need you to output in JSON format like this:
{
  multipleSpecsReason: "", // here explain why you determine this listing to be multiple specs, in Bahasa Indonesia
}

If it's not multiple spec, set`multipleSpecsReason` to be null.

Here is the listing desription:
$description
EOD;
        $answer = $chatGptService->seekAnswer($prompt, 'gpt-4-turbo', 'json_object');
        logger()->info('answer ' . $answer);
        $multiSpecsAnswer = json_decode($answer, true);
        if (is_array($multiSpecsAnswer) && isset($multiSpecsAnswer['multipleSpecsReason'])) {
            return ['Kemungkinan listing ini ada beberapa tipe/model: ' . $multiSpecsAnswer['multipleSpecsReason']];
        } else {
            logger()->error('error decoding answer of LLM multiple specs check');
        }
        return [];
    }

    /**
     * Runs automated review of a listing.
     *
     * Currently this job implements reviewing description vs fields accuracy only.
     * Eventually we should be able to automate all the cases in https://daftarproperti.org/checklist.
     *
     * @return void
     */
    public function handle(ChatGptService $chatGptService, Extractor $extractor)
    {
        try {
            $extractedListing = $extractor->extractSingleListingFromMessage(
                $this->listing->description,
                'gpt-4-turbo',
                'json_object',
            );

            // TODO: No need to convert to JSON back and forth, but the extraction from LLM can be directly array.
            /** @var array<string, mixed> $extracted */
            $extracted = json_decode(type(json_encode($extractedListing))->asString(), true);

            $results = self::listingDiff($extracted, (new ListingResource($this->listing))->resolve());
            // TODO: Add results from field validations as well.
            // Looks like field validations need to be prompted one per field to achieve good accuracy.

            $addressResults = $this->checkAddressFormat($chatGptService, $this->listing->address);
            $results = array_merge($results, $addressResults);

            $multipleSpecsResults = $this->checkMultipleSpecs($chatGptService, $this->listing->description);
            $results = array_merge($results, $multipleSpecsResults);

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
