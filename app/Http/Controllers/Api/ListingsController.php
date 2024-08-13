<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Http\Services\GoogleStorageService;
use App\Helpers\Queue;
use App\Jobs\GenerateListingFromText;
use App\Models\Coordinate;
use App\Models\CancellationNote;
use App\Models\Enums\CancellationStatus;
use App\Models\FilterSet;
use App\Models\GeneratedListing;
use App\Models\Listing;
use App\Models\ListingUser;
use App\Models\Resources\ListingCollection;
use App\Models\Resources\ListingResource;
use App\Models\User;
use App\Repositories\ListingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ListingsController extends Controller
{
    #[OA\Get(
        path: '/api/tele-app/listings',
        tags: ['Listings'],
        summary: 'Get listing items',
        description: 'Returns listing items',
        operationId: 'listings.index',
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                description: 'Search listing by keyword',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'collection',
                in: 'query',
                description: "If set to true, it will only return user's collection",
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'price[min]',
                in: 'query',
                description: 'Minimum price',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'price[max]',
                in: 'query',
                description: 'Maximum price',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'rentPrice[min]',
                in: 'query',
                description: 'Minimum rent price',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'rentPrice[max]',
                in: 'query',
                description: 'Maximum rent price',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'propertyType',
                in: 'query',
                description: 'Property type',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/PropertyType')
            ),
            new OA\Parameter(
                name: 'listingForSale',
                in: 'query',
                description: 'Listing for sale',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'listingForRent',
                in: 'query',
                description: 'Listing for rent',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'bedroomCount',
                in: 'query',
                description: 'Bedroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'bedroomCount[min]',
                in: 'query',
                description: 'Minimum Bedroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'bedroomCount[max]',
                in: 'query',
                description: 'Maximum Bedroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'additionalBedroomCount',
                in: 'query',
                description: 'Additional Bedroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'bathroomCount',
                in: 'query',
                description: 'Bathroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'bathroomCount[min]',
                in: 'query',
                description: 'Minimum Bathroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'bathroomCount[max]',
                in: 'query',
                description: 'Maximum Bathroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'additionalBathroomCount',
                in: 'query',
                description: 'Additional Bathroom count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'lotSize[min]',
                in: 'query',
                description: 'Minimum lot size',
                required: false,

                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'lotSize[max]',

                in: 'query',
                description: 'Maximum lot size',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'buildingSize[min]',
                in: 'query',
                description: 'Minimum building size',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'buildingSize[max]',
                in: 'query',
                description: 'Maximum building size',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'ownership',
                in: 'query',
                description: 'Ownership',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/PropertyOwnership')
            ),
            new OA\Parameter(
                name: 'carCount',
                in: 'query',
                description: 'Car count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'carCount[min]',
                in: 'query',
                description: 'Minimum Car count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'carCount[max]',
                in: 'query',
                description: 'Maximum Car count',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'electricPower',
                in: 'query',
                description: 'Electric Power',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'cityId',
                in: 'query',
                description: 'City Id',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Sort By',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/ListingSort')
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                description: 'Order By',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['asc', 'desc']
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'listings',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Listing')
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request, ListingRepository $repository): JsonResource
    {
        $filterSet = FilterSet::from($request->only([
            'q',
            'collection',
            'price',
            'rentPrice',
            'propertyType',
            'bedroomCount',
            'bathroomCount',
            'lotSize',
            'buildingSize',
            'ownership',
            'carCount',
            'electricPower',
            'city',
            'cityId',
            'sort',
            'order'
        ]));

        if ($request->has('listingForSale')) {
            $filterSet->listingForSale = filter_var($request->input('listingForSale'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('listingForRent')) {
            $filterSet->listingForRent = filter_var($request->input('listingForRent'), FILTER_VALIDATE_BOOLEAN);
        }

        //if collection not present in filters, default set to true
        if (!isset($filterSet->collection)) {
            $filterSet->collection = true;
        }

        if (boolval($filterSet->collection)) {
            $currentUser = Auth::user();
            $currentUserId = $currentUser->user_id ?? 0;
            $filterSet->userId = $currentUserId;
        }

        if (!isset($filterSet->order) && !isset($filterSet->sort)) {
            $filterSet->sort = 'created_at';
            $filterSet->order = 'desc';
        }

        return new ListingCollection($repository->list($filterSet));
    }

    #[OA\Get(
        path: "/api/tele-app/listings/{id}",
        tags: ["Listings"],
        summary: "Get listing by id",
        operationId: "listings.show",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Listing Id",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(ref: "#/components/schemas/Listing")
            ),
            new OA\Response(
                response: 404,
                description: "Listing not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "error",
                            type: "string",
                            example: "Listing not found"
                        )
                    ]
                )
            )
        ]
    )]
    public function show(Listing $listing): JsonResource
    {
        return new ListingResource($listing);
    }

    #[OA\Post(
        path: "/api/tele-app/listings/{id}",
        tags: ["Listings"],
        summary: "Update listing",
        operationId: "listings.update",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Listing Id",
                schema: new OA\Schema(
                    type: "string",
                ),
            ),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(type: "object", ref: "#/components/schemas/ListingRequest"),
            ),
            required: true,
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/Listing",
                ),
            ),
            new OA\Response(
                response: 404,
                description: "Listing not found",
                content: new OA\JsonContent(
                    properties: [new OA\Property(
                        property: "error",
                        type: "string",
                        example: "Listing not found",
                    )]
                ),
            )
        ],
    )]
    public function update(Listing $listing, ListingRequest $request): JsonResource
    {
        $validatedRequest = $request->validated();
        $this->fillCreateUpdateListing($validatedRequest, $listing);
        $listing->save();

        return new ListingResource($listing);
    }

    #[OA\Post(
        path: "/api/tele-app/listings",
        tags: ["Listings"],
        summary: "Create listing",
        operationId: "listings.create",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(type: "object", ref: "#/components/schemas/ListingRequest")
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(ref: "#/components/schemas/Listing")
            )
        ]
    )]
    public function create(ListingRequest $request): JsonResource
    {
        $validatedRequest = $request->validated();
        $listing = new Listing();
        $this->fillCreateUpdateListing($validatedRequest, $listing);
        $listing->user = $this->getListingUser();
        $listing->save();

        return new ListingResource($listing);
    }

    #[OA\Delete(
        path: "/api/tele-app/listings/{id}",
        tags: ["Listings"],
        summary: "Delete listing",
        operationId: "listings.delete",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Listing Id",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Listing deleted successfully"
                        )
                    ]
                )
            )
        ]
    )]
    public function delete(Listing $listing): JsonResponse
    {
        $listing->delete();
        return response()->json(['message' => 'Listing deleted successfully'], 200);
    }

    #[OA\Post(
        path: "/api/tele-app/listings/generate-from-text",
        tags: ["Listings"],
        summary: "Generate Listing from Text",
        operationId: "listings.generateFromText",
        parameters: [
            new OA\Parameter(
                name: "text",
                in: "path",
                required: true,
                description: "Listing Message",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "jobId",
                            type: "string",
                            example: "sample-result-id-1"
                        )
                    ]
                )
            )
        ]
    )]
    public function generateFromText(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $text = $request->input('text');
        if (!is_string($text)) {
            return response()->json(['error' => 'text is not a string'], 400);
        }

        $jobId = Str::uuid()->toString();
        GenerateListingFromText::dispatch($jobId, $text)->onQueue(Queue::getQueueName('generic'));

        return response()->json(['jobId' => $jobId], 200);
    }


    #[OA\Post(
        path: "/api/tele-app/listings/getGenerateResult",
        tags: ["Listings"],
        summary: "Get Generate Listing Result",
        operationId: "listings.getGenerateResult",
        parameters: [
            new OA\Parameter(
                name: "jobId",
                in: "path",
                required: true,
                description: "Generate Listing Id",
                schema: new OA\Schema(
                    type: "string",
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "generatedListing",
                            ref: "#/components/schemas/Listing",
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function getGenerateResult(Request $request): JsonResponse
    {
        $request->validate([
            'jobId' => 'required|string',
        ]);

        $jobId = $request->input('jobId');

        /** @var GeneratedListing|null $jobResult */
        $jobResult = GeneratedListing::where('job_id', $jobId)->first();
        if (!$jobResult) {
            return response()->json(['error' => 'Generated listing not found'], 404);
        }

        return response()->json(['generatedListing' => $jobResult->generated_listing], 200);
    }


    /**
     * @param array<string, mixed> $data
     * @param Listing $listing
     */
    private function fillCreateUpdateListing(array $data, Listing &$listing): void
    {
        $booleanKeys = ['isPrivate', 'withRewardAgreement', 'listingForSale', 'listingForRent', 'isMultipleUnits'];

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                if (in_array($key, $booleanKeys)) {
                    $listing->{$key} = (bool) $value;
                    continue;
                }

                if (is_numeric($value)) {
                    $listing->{$key} = (int) $value;
                    continue;
                }

                $listing->{$key} = $value;
            } else {
                if ($key == 'pictureUrls') {
                    $uploadedImages = $this->uploadImages($value);
                    $listing->pictureUrls = $uploadedImages;
                    continue;
                }

                if ($key == 'coordinate') {
                    $listing->coordinate = Coordinate::from($value);
                    continue;
                }

                $currentData = $listing->{$key};
                if (is_array($currentData)) {
                    $currentData = array_filter($listing->{$key});
                    $updatedData = array_merge($currentData, $value);
                    $listing->{$key} = $updatedData;
                } else {
                    $listing->{$key} = $value;
                }
            }
        }
    }

    private function getListingUser(): ListingUser
    {
        /** @var User $user */
        $user = Auth::user();
        return $user->toListingUser();
    }

    /**
     * @param array<mixed> $images
     *
     * @return array<string>
     */
    private function uploadImages(array $images): array
    {
        $googleStorageService = app()->make(GoogleStorageService::class);

        $uploadedImages = [];
        foreach ($images as $image) {
            if (is_object($image) && is_a($image, \Illuminate\Http\UploadedFile::class)) {
                $fileName = sprintf('%s.%s', md5($image->getClientOriginalName()), $image->getClientOriginalExtension());
                $fileId = time();
                $googleStorageService->uploadFile(
                    type(file_get_contents($image->getRealPath()))->asString(),
                    sprintf('%s_%s', $fileId, $fileName)
                );
                $uploadedImages[] = route('telegram-photo', [$fileId, $fileName], false);
            } else {
                $uploadedImages[] = type($image)->asString();
            }
        }

        return $uploadedImages;
    }

    /**
     * Update the cancellation note for a listing.
     * 
     * @param Request $request
     * @param Listing $listing
     * @return JsonResponse
     */
    public function updateCancellationNote(Request $request, Listing $listing): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $listing->cancellationNote = new CancellationNote(
            reason: $validated['reason'],
            status: CancellationStatus::ON_REVIEW 
        );

        $listing->save();

        return response()->json([
            'success' => true,
            'message' => 'Cancellation note updated successfully.',
        ]); 
    }
}
