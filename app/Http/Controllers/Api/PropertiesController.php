<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FilterSet;
use App\Models\Property;
use App\Models\Resources\PropertyCollection;
use App\Models\Resources\PropertyResource;
use App\Repositories\PropertyRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class PropertiesController extends Controller
{
    #[OA\Get(
        path: '/api/app/properties',
        tags: ['Properties'],
        summary: 'Get list of property',
        description: 'Returns list of property',
        operationId: 'index',
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                description: 'Search property by keyword',
                required: false,
                schema: new OA\Schema(type: 'string')
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
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'properties',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Property')
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request, PropertyRepository $repository): JsonResource
    {
        $filterSet = FilterSet::from($request->only([
            'q',
            'price',
            'rentPrice',
            'propertyType',
            'bedroomCount',
            'bathroomCount',
            'lotSize',
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

        if (!isset($filterSet->order) && !isset($filterSet->sort)) {
            $filterSet->sort = 'created_at';
            $filterSet->order = 'desc';
        }

        return new PropertyCollection($repository->list($filterSet));
    }

    #[OA\Get(
        path: '/api/app/properties/{id}',
        tags: ['Properties'],
        summary: 'Get property by id',
        operationId: 'show',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Property Id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(ref: '#/components/schemas/Property')
            ),
            new OA\Response(
                response: 404,
                description: 'Property not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Property not found')
                    ]
                )
            )
        ]
    )]
    public function show(Property $property): JsonResource
    {
        return new PropertyResource($property);
    }
}
