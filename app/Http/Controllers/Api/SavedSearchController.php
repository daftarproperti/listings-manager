<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavedSearchRequest;
use App\Models\FilterSet;
use App\Models\Resources\SavedSearchCollection;
use App\Models\Resources\SavedSearchResource;
use App\Models\SavedSearch;
use App\Models\User;
use App\Repositories\SavedSearchRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class SavedSearchController extends Controller
{
    #[OA\Get(
        path: '/api/app/saved-searches',
        tags: ['Saved Searches'],
        summary: 'Get saved search items',
        description: 'Returns saved search items',
        operationId: 'saved_searches.index',
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'saved_searches',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/SavedSearch'
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function index(SavedSearchRepository $repository): JsonResource
    {
        /** @var User $user */
        $user = Auth::user();

        $input = [
            'userId' => $user->user_id,
        ];

        return new SavedSearchCollection($repository->list($input));
    }

    #[OA\Get(
        path: '/api/app/saved-searches/{id}',
        tags: ['Saved Searches'],
        summary: 'Get saved search by id',
        operationId: 'saved_searches.show',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Saved Search Id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(ref: '#/components/schemas/SavedSearch')
            ),
            new OA\Response(
                response: 404,
                description: 'Saved search not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Saved search not found'
                        )
                    ]
                )
            )
        ]
    )]
    public function show(SavedSearch $savedSearch): JsonResource
    {
        return new SavedSearchResource($savedSearch);
    }

    #[OA\Post(
        path: '/api/app/saved-searches',
        tags: ['Saved Searches'],
        summary: 'Create saved search',
        operationId: 'saved_searches.create',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'multipart/form-data' => new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        type: 'object',
                        ref: '#/components/schemas/SavedSearchRequest'
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Saved search created successfully'
                        )
                    ]
                )
            )
        ]
    )]
    public function create(SavedSearchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $savedSearch = new SavedSearch();
        $this->setSavedSearchAttribute($data, $savedSearch);
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->user_id;
        $savedSearch->userId = $userId;
        $savedSearch->save();

        return response()->json(['message' => 'Saved search created successfully'], 200);
    }

    #[OA\Post(
        path: '/api/app/saved-searches/{id}',
        tags: ['Saved Searches'],
        summary: 'Update saved searches',
        operationId: 'saved_searches.update',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Saved Searches Id',
                schema: new OA\Schema(
                    type: 'string'
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        type: 'object',
                        ref: '#/components/schemas/SavedSearchRequest'
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Saved search updated successfully'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Saved search not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Saved search not found'
                        )
                    ]
                )
            )
        ]
    )]
    public function update(SavedSearchRequest $request, SavedSearch $savedSearch): JsonResponse
    {
        $data = $request->validated();
        $this->setSavedSearchAttribute($data, $savedSearch);
        $savedSearch->save();

        return response()->json(['message' => 'Saved search updated successfully'], 200);
    }

    #[OA\Delete(
        path: '/api/app/saved-searches/{id}',
        tags: ['Saved Searches'],
        summary: 'Delete saved searches',
        operationId: 'saved_searches.delete',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Saved Searches Id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Saved search deleted successfully'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Saved search not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Saved search not found'
                        )
                    ]
                )
            )
        ]
    )]
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
