<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Assert;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Http\Services\GoogleStorageService;
use App\Models\Listing;
use App\Models\ListingUser;
use App\Models\Resources\ListingCollection;
use App\Models\Resources\ListingResource;
use App\Models\TelegramUser;
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
     *        name="type",
     *        description="Property type",
     *        required=false,
     *        @OA\Schema(
     *            type="string",
     *            enum={"house", "apartment", "land"}
     *        )
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
     *        @OA\Schema(
     *            type="string",
     *            enum={"shm", "hgb", "girik", "lainnya"}
     *        )
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
     *        @OA\Schema(
     *            type="string",
     *            enum={"price", "bedroomCount", "lotSize"}
     *        )
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
        $filters = $request->only([
            'q',
            'collection',
            'price',
            'type',
            'bedroomCount',
            'bathroomCount',
            'lotSize',
            'lotSize',
            'buildingSize',
            'ownership',
            'carCount',
            'electricPower',
            'sort',
            'order'
        ]);

        //if collection not present in filters, default set to true
        if (!isset($filters['collection'])) {
            $filters['collection'] = true;
        }

        if (boolval($filters['collection'])) {
            $currentUserId = app(TelegramUser::class)->user_id ?? null;
            $filters['userId'] = $currentUserId;
        }

        return new ListingCollection($repository->list($filters));
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
        foreach ($data as $key => $value) {
            if(!is_array($value)) {
                if ($key == 'isPrivate') {
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
        $user = app(TelegramUser::class);
        $listingUser = new ListingUser();
        $listingUser->name = $user->first_name . ' ' . ($user->last_name ?? '');
        $listingUser->userName = $user->username ?? null;
        $listingUser->userId = (int) $user->user_id;
        $listingUser->source = 'telegram';

        return $listingUser;
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
        foreach ($images as $image)
        {
            if (is_object($image) && is_a($image, \Illuminate\Http\UploadedFile::class)) {
                $fileName = sprintf('%s.%s', md5($image->getClientOriginalName()) , $image->getClientOriginalExtension());
                $fileId = time();
                $googleStorageService->uploadFile(
                    Assert::string(file_get_contents($image->getRealPath())),
                    sprintf('%s_%s', $fileId, $fileName)
                );
                $uploadedImages[] = route('telegram-photo', [$fileId, $fileName], false);
            } else {
                $uploadedImages[] = Assert::string($image);
            }
        }

        return $uploadedImages;
    }
}
