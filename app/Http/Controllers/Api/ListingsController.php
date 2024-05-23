<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DPAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Http\Services\GoogleStorageService;
use App\Models\Enums\VerifyStatus;
use App\Models\FilterSet;
use App\Models\Listing;
use App\Models\ListingUser;
use App\Models\Resources\ListingCollection;
use App\Models\Resources\ListingResource;
use App\Repositories\ListingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
                name: 'propertyType',
                in: 'query',
                description: 'Property type',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/PropertyType')
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
            'propertyType',
            'bedroomCount',
            'bathroomCount',
            'lotSize',
            'buildingSize',
            'ownership',
            'carCount',
            'electricPower',
            'city',
            'sort',
            'order'
        ]));

        //if collection not present in filters, default set to true
        if (!isset($filterSet->collection)) {
            $filterSet->collection = true;
        }

        if (boolval($filterSet->collection)) {
            $currentUser = DPAuth::getUser();
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
        $listing->verifyStatus = VerifyStatus::ON_REVIEW;
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


    /**
     * @param array<string, mixed> $data
     * @param Listing $listing
     */
    private function fillCreateUpdateListing(array $data, Listing &$listing): void
    {
        $booleanKeys = ['isPrivate', 'listingForSale', 'listingForRent'];

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

                $currentData = $listing->{$key} ? array_filter($listing->{$key}) : [];
                $updatedData = array_merge($currentData, $value);
                $listing->{$key} = $updatedData;
            }
        }
    }

    private function getListingUser(): ListingUser
    {
        $user = DPAuth::getUser();
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
}
