<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resources\CityCollection;
use App\Models\Resources\CityResource;
use App\Repositories\CityRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class CityController extends Controller
{
    #[OA\Get(
        path: '/api/tele-app/cities',
        tags: ['Cities'],
        summary: 'Get cities',
        description: 'Returns city items',
        operationId: 'cities.index',
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                description: 'Search city by keyword',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'cities',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/City')
                        )
                    ]
                )
            )
        ]
    )]

    public function index(Request $request, CityRepository $cityRepository): JsonResource
    {
        $q = type($request->input('q', ''))->asString();
        $keyWord =  strlen($q) >= 3 ? $q : null;

        $cities = $cityRepository->searchByKeyword($keyWord);

        return new CityCollection($cities);
    }

    #[OA\Get(
        path: "/api/tele-app/cities/{id}",
        tags: ["Cities"],
        summary: "Get city by id",
        operationId: "cities.getCityById",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "City Id",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(ref: "#/components/schemas/City")
            ),
            new OA\Response(
                response: 404,
                description: "City not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "error",
                            type: "string",
                            example: "City not found"
                        )
                    ]
                )
            )
        ]
    )]
    public function getCityById(int $id, CityRepository $cityRepository): JsonResource
    {
        $city = $cityRepository->getCityById($id);

        return $city? new CityResource($city) : abort(404, 'City not found');
    }
}
