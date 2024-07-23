<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use App\Models\ListingType;
use App\Models\PropertyType;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ListingRequest",
    type: "object"
)]
class ListingRequest extends BaseApiRequest
{
    #[OA\Property(property: "title", type: "string", example: "Rumah dijual di daerah pasteur")]
    #[OA\Property(property: "address", type: "string", example: "Jl. Pendidikan No. 1")]
    #[OA\Property(property: "description", type: "string", example: "Rumah bagus")]
    #[OA\Property(property: "price", type: "integer", example: 100000)]
    #[OA\Property(property: "rentPrice", type: "integer", example: 40000)]
    #[OA\Property(property: "lotSize", type: "integer", example: 1000)]
    #[OA\Property(property: "buildingSize", type: "integer", example: 2000)]
    #[OA\Property(property: "carCount", type: "integer", example: 4)]
    #[OA\Property(property: "bedroomCount", type: "integer", example: 3)]
    #[OA\Property(property: "additionalBedroomCount", type: "integer", example: 3)]
    #[OA\Property(property: "bathroomCount", type: "integer", example: 2)]
    #[OA\Property(property: "additionalBathroomCount", type: "integer", example: 2)]
    #[OA\Property(property: "floorCount", type: "integer", example: 2)]
    #[OA\Property(property: "electricPower", type: "integer", example: 2200)]
    #[OA\Property(property: "facing", type: "string", example: "Utara")]
    #[OA\Property(property: "ownership", type: "string", example: "SHM")]
    #[OA\Property(property: "city", type: "string", example: "Bandung")]
    #[OA\Property(property: "cityId", type: "integer", example: 1)]
    #[OA\Property(property: "listingType", ref: "#/components/schemas/ListingType", example: "Dijual")]
    #[OA\Property(property: "propertyType", ref: "#/components/schemas/PropertyType", example: "Rumah")]
    #[OA\Property(property: "listingForRent", type: "boolean", example: false)]
    #[OA\Property(property: "listingForSale", type: "boolean", example: false)]
    #[OA\Property(property: "pictureUrls", type: "array", items: new OA\Items(
        oneOf: [
            new OA\Schema(type: "string", format: "binary", example: "\x00\x00\x00\x04\x00\x00\x00\x04"),
            new OA\Schema(type: "string", format: "url", example: "https://example.com/image.jpg"),
        ]
    ))]
    #[OA\Property(property: "coordinate", type: "object", properties: [
        new OA\Property(property: "latitude", type: "integer"),
        new OA\Property(property: "longitude", type: "integer")
    ])]
    #[OA\Property(property: "isPrivate", type: "boolean", example: false)]
    #[OA\Property(property: "withRewardAgreement", type: "boolean", example: true)]
    #[OA\Property(property: "isMultipleUnits", type: "boolean", example: true)]

    public function authorize()
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'address' => 'required|string',
            'description' => 'required|string',
            'price' => 'required_if:listingForSale,true|numeric',
            'rentPrice' => 'required_if:listingForRent,true|numeric',
            'buildingSize' => 'required_unless:propertyType,land,unknown|numeric',
            'lotSize' => 'required_unless:propertyType,apartment,unknown|numeric',
            'city' => 'nullable|string',
            'cityId' => 'required|integer',
            'bedroomCount' => 'required_unless:propertyType,land,warehouse,unknown|numeric',
            'additionalBedroomCount' => 'nullable|numeric',
            'bathroomCount' => 'required_unless:propertyType,land,warehouse,unknown|numeric',
            'additionalBathroomCount' => 'nullable|numeric',
            'carCount' => 'nullable|numeric',
            'floorCount' => 'nullable|numeric',
            'electricPower' => 'nullable|numeric',
            'facing' => 'nullable|string',
            'ownership' => 'nullable|string',
            'pictureUrls' => 'nullable|array|max:10',
            'coordinate.latitude' => 'nullable|string',
            'coordinate.longitude' => 'nullable|string',
            'isPrivate' => 'required|boolean',
            'withRewardAgreement' => 'required|boolean',
            'isMultipleUnits' => 'required|boolean',
            'listingType' => ['nullable', Rule::in(ListingType::cases())],
            'propertyType' => ['nullable', Rule::in(PropertyType::cases())],
            'listingForSale' => 'required|boolean',
            'listingForRent' => 'required|boolean',
        ];
    }
}
