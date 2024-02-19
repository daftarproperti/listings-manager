<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Assert;
use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Http\Services\GoogleStorageService;
use App\Models\Property;
use App\Models\PropertyUser;
use App\Models\Resources\PropertyCollection;
use App\Models\Resources\PropertyResource;
use App\Models\TelegramUser;
use App\Repositories\PropertyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertiesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tele-app/properties",
     *     tags={"Properties"},
     *     summary="Get list of property",
     *     description="Returns list of property",
     *     operationId="index",
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
     *        name="bedroom_count",
     *        description="Bedroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="bathroom_count",
     *        description="Bathroom count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="lot_size[min]",
     *        description="Minimum lot size",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="lot_size[max]",
     *        description="Maximum lot size",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="building_size[min]",
     *        description="Minimum building size",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="building_size[max]",
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
     *        name="car_count",
     *        description="Car count",
     *        required=false,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *     ),
     *     @OA\Parameter(
     *        in="query",
     *        name="electric_power",
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
     *            enum={"price", "bedroom_count", "lot_size"}
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
     *                  property="properties",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Property",
     *                  ),
     *              )
     *          ),
     *     )
     * )
     */

    public function index(Request $request, PropertyRepository $repository): JsonResource
    {
        $filters = $request->only([
            'collection',
            'price',
            'type',
            'bedroom_count',
            'bathroom_count',
            'lot_size',
            'lot_size',
            'building_size',
            'ownership',
            'car_count',
            'electric_power',
            'sort',
            'order'
        ]);

        //if collection not present in filters, default set to true
        if (!isset($filters['collection'])) {
            $filters['collection'] = true;
        }

        if (boolval($filters['collection'])) {
            $currentUserId = app(TelegramUser::class)->user_id ?? null;
            $filters['user_id'] = $currentUserId;
        }

        return new PropertyCollection($repository->list($filters));
    }

    /**
     * @OA\Get(
     *     path="/api/tele-app/properties/{id}",
     *     tags={"Properties"},
     *     summary="Get property by id",
     *     operationId="show",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Property Id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/Property"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Property not found"
     *              )
     *         ),
     *     ),
     * )
     */

    public function show(Property $property): JsonResource
    {
        return new PropertyResource($property);
    }

    /**
     * @OA\Post(
     *     path="/api/tele-app/properties/{id}",
     *     tags={"Properties"},
     *     summary="Update property",
     *     operationId="update",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Property Id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(type="object", ref="#/components/schemas/PropertyRequest")
     *          ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/Property"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Property not found"
     *              )
     *         ),
     *     ),
     * )
     */

    public function update(Property $property, PropertyRequest $request): JsonResource
    {
        $validatedRequest = $request->validated();
        $this->fillCreateUpdateProperty($validatedRequest, $property);
        $property->save();

        return new PropertyResource($property);
    }

    /**
     * @OA\Post(
     *     path="/api/tele-app/properties",
     *     tags={"Properties"},
     *     summary="Create property",
     *     operationId="create",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(type="object", ref="#/components/schemas/PropertyRequest")
     *          ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/Property"
     *         ),
     *     )
     * )
     */

     public function create(PropertyRequest $request): JsonResource
     {
         $validatedRequest = $request->validated();
         $property = new Property();
         $this->fillCreateUpdateProperty($validatedRequest, $property);
         $property->user = $this->getPropertyUser();
         $property->save();

         return new PropertyResource($property);
     }

    /**
     * @OA\Delete(
     *     path="/api/tele-app/properties/{id}",
     *     tags={"Properties"},
     *     summary="Delete property",
     *     operationId="delete",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Property Id",
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
     *                  example="Property deleted successfully"
     *              )
     *         ),
     *     )
     * )
     **/
    public function delete(Property $property): JsonResponse
    {
        $property->delete();
        return response()->json(['message' => 'Property deleted successfully'], 200);
    }


    /**
     * @param array<string, mixed> $data
     * @param Property $property
     */
    private function fillCreateUpdateProperty(array $data, Property &$property): void
    {
        foreach ($data as $key => $value) {
            if(!is_array($value)) {
                if ($key == 'isPrivate') {
                    $property->{$key} = (bool) $value;
                    continue;
                }

                if (is_numeric($value)) {
                    $property->{$key} = (int) $value;
                    continue;
                }

                $property->{$key} = $value;
            } else {
                if ($key == 'pictureUrls') {
                    $uploadedImages = $this->uploadImages($value);
                    $property->pictureUrls = $uploadedImages;
                    continue;
                }

                $currentData = $property->{$key} ? array_filter($property->{$key}) : [];
                $updatedData = array_merge($currentData, $value);
                $property->{$key} = $updatedData;
            }
        }
    }

    private function getPropertyUser(): PropertyUser
    {
        $user = app(TelegramUser::class);
        $propertyUser = new PropertyUser();
        $propertyUser->name = $user->first_name . ' ' . ($user->last_name ?? '');
        $propertyUser->userName = $user->username ?? null;
        $propertyUser->userId = (int) $user->user_id;
        $propertyUser->source = 'telegram';

        return $propertyUser;
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
                $uploadedImages[] = route('telegram-photo', [$fileId, $fileName]);
            } else {
                $uploadedImages[] = Assert::string($image);
            }
        }

        return $uploadedImages;
    }
}
