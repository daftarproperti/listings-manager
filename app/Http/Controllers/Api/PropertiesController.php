<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Services\GoogleStorageService;
use App\Models\Property;
use App\Models\Resources\PropertyCollection;
use App\Models\Resources\PropertyResource;
use App\Models\TelegramUser;
use App\Repositories\PropertyRepository;
use Illuminate\Http\Request;

class PropertiesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tele-app/properties",
     *     tags={"Properties"},
     *     summary="Get list of property",
     *     description="Returns list of property",
     *     operationId="index",
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

    public function index(Request $request, PropertyRepository $repository)
    {
        $filters = $request->only([
            'collection',
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

    public function show(Property $property)
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
     *              @OA\Schema(type="object", ref="#/components/schemas/UpdatePropertyRequest")
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

    public function update(Property $property, UpdatePropertyRequest $request)
    {
        $validatedRequest = $request->validated();
        $this->fillUpdateProperty($validatedRequest, $property);
        $property->save();

        return new PropertyResource($property);
    }

    private function fillUpdateProperty($data, &$property)
    {
        foreach ($data as $key => $value) {
            if(!is_array($value)) {
                $property->{$key} = $key == 'isPrivate' ? (bool) $value : $value;
            } else {
                if ($key == 'pictureUrls') {
                    $uploadedImages = $this->uploadImages($value);
                    $property->pictureUrls = $uploadedImages;
                    continue;
                }

                $currentData = array_filter($property->{$key});
                $updatedData = array_merge($currentData, $value);
                $property->{$key} = $updatedData;
            }
        }
    }

    private function uploadImages($images)
    {
        $googleStorageService = app()->make(GoogleStorageService::class);

        $uploadedImages = [];
        foreach ($images as $image)
        {
            if (is_a($image, \Illuminate\Http\UploadedFile::class)) {
                $fileName = sprintf('%s.%s', md5($image->getClientOriginalName()) , $image->getClientOriginalExtension());
                $flieId = time();
                $googleStorageService->uploadFile(file_get_contents($image->getRealPath()), sprintf('%s_%s', $flieId, $fileName));
                $uploadedImages[] = route('telegram-photo', [$flieId, $fileName]);
            } else {
                $uploadedImages[] = (string) $image;
            }
        }

        return $uploadedImages;
    }
}
