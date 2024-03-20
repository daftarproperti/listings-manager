<?php

namespace App\Http\Controllers\Api;

use App\DTO\FilterSet;
use App\Http\Controllers\Controller;
use App\Models\Property;
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
     *        name="q",
     *        description="Search property by keyword",
     *        required=false,
     *        @OA\Schema(
     *            type="string"
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
        $filterSet = FilterSet::from($request->only([
            'q',
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
        ]));

        if (!isset($filterSet->order) && !isset($filterSet->sort)) {
            $filterSet->sort = 'created_at';
            $filterSet->order = 'desc';
        }

        return new PropertyCollection($repository->list($filterSet));
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
}
