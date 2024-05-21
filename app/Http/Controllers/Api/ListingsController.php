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

class ListingsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tele-app/listings",
     *     tags={"Listings"},
     *     summary="Get listing items",
     *     description="Returns listing items",
     *     operationId="listings.index",
     *     @OA\Parameter(
     *        in="query",
     *        name="q",
     *        description="Search listing by keyword",
     *        required=false,
     *        @OA\Schema(
     *            type="string"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="collection",
     *        description="If set to true, it will only return user's collection",
     *        required=false,
     *        @OA\Schema(
     *            type="boolean"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="price[min]",
     *        description="Minimum price",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="price[max]",
     *        description="Maximum price",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="propertyType",
     *        description="Property type",
     *        required=false,
     *        @OA\Schema(ref="#/components/schemas/PropertyType")
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="bedroomCount",
     *        description="Bedroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="bedroomCount[min]",
     *        description="Minimum Bedroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="bedroomCount[max]",
     *        description="Maximum Bedroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="bathroomCount",
     *        description="Bathroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="bathroomCount[min]",
     *        description="Minimum Bathroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="bathroomCount[max]",
     *        description="Maximum Bathroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="lotSize[min]",
     *        description="Minimum lot size",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="lotSize[max]",
     *        description="Maximum lot size",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="buildingSize[min]",
     *        description="Minimum building size",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="buildingSize[max]",
     *        description="Maximum building size",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="ownership",
     *        description="Ownership",
     *        required=false,
     *        @OA\Schema(ref="#/components/schemas/PropertyOwnership")
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="carCount",
     *        description="Car count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="carCount[min]",
     *        description="Minimum Car count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="carCount[max]",
     *        description="Maximum Car count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="electricPower",
     *        description="Electric Power",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="sort",
     *        description="Sort By",
     *        required=false,
     *        @OA\Schema(ref="#/components/schemas/ListingSort")
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="order",
     *        description="Order By",
     *        required=false,
     *        @OA\Schema(
     *            type="string",
     *            enum={"asc", "desc"}
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="listings",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Listing",
     *                  ),
     *              )
     *          ),
     *     )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/api/tele-app/listings/{id}",
     *     tags={"Listings"},
     *     summary="Get listing by id",
     *     operationId="listings.show",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Listing Id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/Listing"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Listing not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Listing not found"
     *              )
     *         ),
     *     ),
     * )
     */

    public function show(Listing $listing): JsonResource
    {
        return new ListingResource($listing);
    }

    /**
     * @OA\Post(
     *     path="/api/tele-app/listings/{id}",
     *     tags={"Listings"},
     *     summary="Update listing",
     *     operationId="listings.update",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Listing Id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(type="object", ref="#/components/schemas/ListingRequest")
     *          ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/Listing"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Listing not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Listing not found"
     *              )
     *         ),
     *     ),
     * )
     */

    public function update(Listing $listing, ListingRequest $request): JsonResource
    {
        $validatedRequest = $request->validated();
        $this->fillCreateUpdateListing($validatedRequest, $listing);
        $listing->save();

        return new ListingResource($listing);
    }

    /**
     * @OA\Post(
     *     path="/api/tele-app/listings",
     *     tags={"Listings"},
     *     summary="Create listing",
     *     operationId="listings.create",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(type="object", ref="#/components/schemas/ListingRequest")
     *          ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/Listing"
     *         ),
     *     )
     * )
     */

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

    /**
     * @OA\Delete(
     *     path="/api/tele-app/listings/{id}",
     *     tags={"Listings"},
     *     summary="Delete listing",
     *     operationId="listings.delete",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Listing Id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Listing deleted successfully"
     *              )
     *         ),
     *     )
     * )
     **/
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
