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
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

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
        $answer = $chatGptService->seekAnswer($prompt, 'gpt-4-turbo', ['type' => 'json_object']);
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
        $answer = $chatGptService->seekAnswer($prompt, 'gpt-4-turbo', ['type' => 'json_object']);
        $multiSpecsAnswer = json_decode($answer, true);
        if (is_array($multiSpecsAnswer) && isset($multiSpecsAnswer['multipleSpecsReason'])) {
            return ['Kemungkinan listing ini ada beberapa tipe/model: ' . $multiSpecsAnswer['multipleSpecsReason']];
        } else {
            logger()->error('error decoding answer of LLM multiple specs check');
        }
        return [];
    }

    /**
     * @return array<string> Messages to be displayed to user
     */
    private function checkNoContact(ChatGptService $chatGptService, string $description): array
    {
        $prompt = <<<EOD
I am going to give you a real estate listing description in Indonesia. I want you to review whether this listing
contains a contact information of the listing registrant, because we prohibit such content since we want the registrant
to only provide a contact number in a separate field.

A listing is considered containing contact information if it mentions about phone number or social media handles, but
it is okay to mention registrant name and company. A phone number in Indonesia may look like +62xxx or 08xxx, and
sometimes this is encoded not in a standard way, e.g. using spaces between numbers, or using emoji numbers instead.
You should be able to detect variations of this.

I need you to output in JSON format like this:
{
  // here explain in Bahasa Indonesia why you determine this listing contains a contact information.
  containsContactReason: "",
}

If it does not contain contact information, set the `containsContactReason` to null.

Here is the listing desription:
$description
EOD;
        $answer = $chatGptService->seekAnswer($prompt, 'gpt-4-turbo', ['type' => 'json_object']);
        $multiSpecsAnswer = json_decode($answer, true);
        if (is_array($multiSpecsAnswer) && isset($multiSpecsAnswer['containsContactReason'])) {
            return [
                'Kemungkinan listing ini mengandung informasi kontak: ' . $multiSpecsAnswer['containsContactReason'],
            ];
        } else {
            logger()->error('error decoding answer of LLM no contact check');
        }
        return [];
    }
    /**
     * Get a base64 encoded data URL representation of an image URL. In case of failure, exception shall be thrown
     *
     * @param string $url URL to the image
     *
     * @return string base64 encoded data URL of the image file
     */
    private function imageToBase64EncodedDataURL(string $url): string
    {
        $response = Http::get($url);
        $reason = $response->body();
        if ($response->failed()) {
            throw new \ErrorException('Failed to fetch image from URL: ' . $reason);
        }

        // Aliasing for readability
        $imageData = $reason;

        // Determine the MIME type (e.g., image/jpeg, image/png)
        $imageInfo = getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            throw new \ErrorException('Failed to get image information');
        }
        $mimeType = $imageInfo['mime']; // e.g., "image/jpeg"

        // Convert to base64
        $base64Image = base64_encode($imageData);

        // Create a Data URL
        $dataUrl = "data:$mimeType;base64,$base64Image";

        return $dataUrl;
    }
    /**
     * Create a temporary file in the given directory
     *
     * @param string $directory directory to create the temporary file name in, sys_get_temp_dir() recommended
     * @param string $prefix    prefix to prepend to the temporary file name
     * @param string $extension extension of the temporary file name, e.g.: jpg (right, without the dot)
     */
    private function createTemporaryFileWithPrefix(string $directory, string $prefix, string $extension): string
    {
        $initTempFileName = tempnam($directory, $prefix);
        if ($initTempFileName === false) {
            throw new \ErrorException('Failed to create temporary file');
        }
        $tempFileName = $initTempFileName . '.' . $extension;
        File::move($initTempFileName, $tempFileName);

        return $tempFileName;
    }
    /**
     * Query street view for images at given $latitude and $longitude pair, optionally setting $pitch then stitch the
     * images using a script calling OpenCV Stitcher (currently written in Python due to missing Stitcher class in the
     * latest but unofficial PHP binding php-opencv). Resulting image will be saved in a temporary file that's not
     * automatically deleted, so please DON'T FORGET TO DELETE IT after use. Failure to get any of the images or any
     * file related operations will result in exception being thrown
     *
     * @param string $apiKey Google API key capable of hitting Street View Static Image API
     * @param float|null $latitude latitude of the location
     * @param float|null $longitude longitude of the location
     * @param int $pitch in case you want to see up/down, as degrees (-90..90, default is looking straight at 0° degree
     *            angle)
     *
     * @return string path to the stitched panorama image
     */
    private function get360PanoramaStreetView(
        string $apiKey,
        float|null $latitude,
        float|null $longitude,
        int $pitch = 0,
    ): string {
        $latlong = $latitude . ',' . $longitude;
        $tempDir = sys_get_temp_dir();

        try {
            $imagePaths = [];
            foreach ([0,90,180,270] as $heading) {
                // 640x640 is the maximum limit, tried 650x650 but it still returns 640x640
                $url = "https://maps.googleapis.com/maps/api/streetview?size=640x640&location=$latlong&fov=120&heading="
                     . "$heading&pitch=$pitch&key=$apiKey";
                $response = Http::get($url);
                $reason = $response->body();
                if ($response->failed()) {
                    throw new \ErrorException('Failed to fetch image from URL: ' . $reason);
                }

                // aliasing for readability
                $imageBlob = $reason;
                $tempFileName = $this->createTemporaryFileWithPrefix($tempDir, '360', 'jpg');
                $imagePaths[] = $tempFileName;
                File::put($tempFileName, $imageBlob);
            }

            $tempFileName = $this->createTemporaryFileWithPrefix($tempDir, '360', 'jpg');

            $success = false;
            // lower confidence threshold may result in a wider image, promoting chances of more successful housing(s)
            // identification, but doesn't always work, hence the iterative attempt
            foreach ([0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0] as $confidenceThreshold) {
                $pythonPath = type(config('python.interpreter_path'))->asString();
                $scriptPath = base_path('app/Scripts/Python/pano_stitch.py');
                $command = [
                    $pythonPath,
                    $scriptPath,
                    ...$imagePaths,
                    $confidenceThreshold,
                    $tempFileName,
                ];

                $result = Process::run($command);
                if ($result->successful()) {
                    // we just need 1 successful attempt
                    $success = true;
                    break;
                }

                // do we need this? I mean, it may not happen only due to confidence threshold being too low...
                Log::warning('Failed to execute panorama stitcher script: ' . $result->errorOutput());
            }

            // if all attempts fail, throw exception
            if (!$success) {
                throw new \ErrorException('Failed to stitch panorama');
            }
        } finally {
            // delete the partial images in any cases for a clean exit
            File::delete($imagePaths);
        }

        return $tempFileName;
    }
    /**
     * Try to give a verdict of uploaded images by validating it against street view images of the listing's coordinate
     *
     * @param $chatGptService ChatGptService instance used to prompt the AI to do validation
     *
     * @return mixed containing indices of uploaded images (they seem ordered, so this should be reliable), 1-based,
     *               may be empty, may be null in a very specific corner case where the returned JSON is not valid
     */
    public function validateUploadedImages(ChatGptService $chatGptService): mixed
    {
        $apikey = type(config('services.google.maps_api_key'))->asString();
        $panoramaFileName = $this->get360PanoramaStreetView(
            $apikey,
            $this->listing->coordinate->latitude,
            $this->listing->coordinate->longitude,
        );

        $content = [
            [
                'type' => 'text',
                'text' =>  'The first image is the haystack. The rest of the images are needles. I need you to find '
                          . 'housing(s) in the needles, then find whether the same housing(s) exist(s) in the haystack.'
                          . 'Answer as an array of image numbers (1, 2, and so on) whose housing(s) are found. Return '
                          . 'empty array if none found.'
                          // . 'Explain your answer.' // uncomment this for debugging, e.g. to read how the AI
                                                      // determines its verdict
                          ,
            ],
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $this->imageToBase64EncodedDataURL($panoramaFileName),
                ],
            ],
        ];

        // we're done with the panorama file
        File::delete($panoramaFileName);

        foreach ($this->listing->pictureUrls as $pictureUrl) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $this->imageToBase64EncodedDataURL($pictureUrl),
                ],
            ];
        }

        $responseFormat = [
            'type' => 'json_schema',
            'json_schema' => [
                'strict' => true,
                'name' => 'IdentificationResult',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'result' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'integer',
                            ],
                        ],
                    ],
                    'required' => ['result'],
                    'additionalProperties' => false,
                ],
            ],
        ];
        // $responseFormat = ['type' => 'text']; // uncomment this for debugging, sync with 'Explain your answer' above

        return json_decode($chatGptService->seekAnswer($content, 'gpt-4o-mini', $responseFormat), true);
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
                ['type' => 'json_object'],
            );

            // TODO: No need to convert to JSON back and forth, but the extraction from LLM can be directly array.
            /** @var array<string, mixed> $extracted */
            $extracted = json_decode(type(json_encode($extractedListing))->asString(), true);

            $results = self::listingDiff($extracted, (new ListingResource($this->listing))->resolve());

            $addressResults = $this->checkAddressFormat($chatGptService, $this->listing->address);
            $results = array_merge($results, $addressResults);

            $multipleSpecsResults = $this->checkMultipleSpecs($chatGptService, $this->listing->description);
            $results = array_merge($results, $multipleSpecsResults);

            $noContactResults = $this->checkNoContact($chatGptService, $this->listing->description);
            $results = array_merge($results, $noContactResults);

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
