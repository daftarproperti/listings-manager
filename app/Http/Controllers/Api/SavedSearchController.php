<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavedSearchRequest;
use App\Models\FilterSet;
use App\Models\Resources\SavedSearchCollection;
use App\Models\Resources\SavedSearchResource;
use App\Models\SavedSearch;
use App\Models\TelegramUser;
use App\Repositories\SavedSearchRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedSearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tele-app/saved-searches",
     *     tags={"Saved Searches"},
     *     summary="Get saved search items",
     *     description="Returns saved search items",
     *     operationId="saved_searches.index",
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="saved_searches",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/SavedSearch",
     *                  ),
     *              )
     *          ),
     *     )
     * )
     */

    public function index(SavedSearchRepository $repository): JsonResource
    {
        $input = [
            'userId' => app(TelegramUser::class)->user_id,
        ];
        return new SavedSearchCollection($repository->list($input));
    }

    /**
     * @OA\Get(
     *     path="/api/tele-app/saved-searches/{id}",
     *     tags={"Saved Searches"},
     *     summary="Get saved search by id",
     *     operationId="saved_searches.show",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Saved Search Id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/SavedSearch"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Saved search not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Saved search not found"
     *              )
     *         ),
     *     ),
     * )
     */

    public function show(SavedSearch $savedSearch): JsonResource
    {
        return new SavedSearchResource($savedSearch);
    }

    /**
     * @OA\Post(
     *     path="/api/tele-app/saved-searches",
     *     tags={"Saved Searches"},
     *     summary="Create saved search",
     *     operationId="saved_searches.create",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(type="object", ref="#/components/schemas/SavedSearchRequest")
     *          ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Saved search created successfully"
     *              )
     *         ),
     *     )
     * )
     */

    public function create(SavedSearchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $savedSearch = new SavedSearch;
        $this->setSavedSearchAttribute($data, $savedSearch);
        $userId = app(TelegramUser::class)->user_id;
        $savedSearch->userId = $userId;
        $savedSearch->save();

        return response()->json(['message' => 'Saved search created successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tele-app/saved-searches/{id}",
     *     tags={"Saved Searches"},
     *     summary="Update saved searches",
     *     operationId="saved_searches.update",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Saved Searches Id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(type="object", ref="#/components/schemas/SavedSearchRequest")
     *          ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Saved search updated successfully"
     *              )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Saved search not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Saved search not found"
     *              )
     *         ),
     *     ),
     * )
     */

    public function update(SavedSearchRequest $request, SavedSearch $savedSearch): JsonResponse
    {
        $data = $request->validated();
        $this->setSavedSearchAttribute($data, $savedSearch);
        $savedSearch->save();

        return response()->json(['message' => 'Saved search updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/tele-app/saved-searches/{id}",
     *     tags={"Saved Searches"},
     *     summary="Delete saved searches",
     *     operationId="saved_searches.delete",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Saved Searches Id",
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
     *                  example="Saved search deleted successfully"
     *              )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Saved search not found",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  example="Saved search not found"
     *              )
     *         ),
     *     ),
     * )
     */

    public function delete(SavedSearch $savedSearch): JsonResponse
    {
        $savedSearch->delete();
        return response()->json(['message' => 'Saved search deleted successfully'], 200);
    }

    /**
     * @param array<string, mixed> $data
     * @param SavedSearch $savedSearch
     */

    private function setSavedSearchAttribute(array $data, SavedSearch $savedSearch): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && $key == 'filterSet') {
                $savedSearch->filterSet = FilterSet::from($value);
                continue;
            }

            $savedSearch->{$key} = $value;
        };
    }
}
