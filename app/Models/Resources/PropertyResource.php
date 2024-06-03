<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Property",
    type: "object"
)]
class PropertyResource extends JsonResource
{
    #[OA\Property(property: "id", type: "string")]
    #[OA\Property(property: "sourceText", type: "string")]
    #[OA\Property(property: "title", type: "string")]
    #[OA\Property(property: "propertyType", ref: "#/components/schemas/PropertyType")]
    #[OA\Property(property: "listingType", ref: "#/components/schemas/ListingType")]
    #[OA\Property(property: "listingForSale", type: "boolean")]
    #[OA\Property(property: "listingForRent", type: "boolean")]
    #[OA\Property(property: "address", type: "string")]
    #[OA\Property(property: "description", type: "string")]
    #[OA\Property(property: "price", type: "integer")]
    #[OA\Property(property: "rentPrice", type: "integer")]
    #[OA\Property(property: "lotSize", type: "integer")]
    #[OA\Property(property: "buildingSize", type: "integer")]
    #[OA\Property(property: "carCount", type: "integer")]
    #[OA\Property(property: "bedroomCount", type: "integer")]
    #[OA\Property(property: "bathroomCount", type: "integer")]
    #[OA\Property(property: "floorCount", type: "integer")]
    #[OA\Property(property: "electricPower", type: "integer")]
    #[OA\Property(property: "facing", ref: "#/components/schemas/FacingDirection")]
    #[OA\Property(property: "ownership", ref: "#/components/schemas/PropertyOwnership")]
    #[OA\Property(property: "verifyStatus", ref: "#/components/schemas/VerifyStatus")]
    #[OA\Property(property: "cityId", type: "integer")]
    #[OA\Property(property: "cityName", type: "string")]
    #[OA\Property(property: "city", type: "string")]
    #[OA\Property(
        property: "pictureUrls",
        type: "array",
        items: new OA\Items(type: "string", format: "uri", example: "https://example.com/image.jpg")
    )]
    #[OA\Property(
        property: "coordinate",
        type: "object",
        properties: [
            new OA\Property(property: "latitude", type: "integer"),
            new OA\Property(property: "longitude", type: "integer"),
        ]
    )]
    #[OA\Property(property: "updatedAt", type: "string", format: "date-time")]
    #[OA\Property(property: "listings", type: "array", items: new OA\Items(ref: "#/components/schemas/Listing"))]

    public static $wrap = null;
    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\Property $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'sourceText' => $prop->sourceText,
            'title' => $prop->title,
            'propertyType' => $prop->propertyType,
            'listingType' => $prop->listingType,
            'listingForSale' => $prop->listingForSale ?? false,
            'listingForRent' => $prop->listingForRent ?? false,
            'address' => $prop->address,
            'description' => $prop->description,
            'price' => $prop->price ? (int) $prop->price : null,
            'rentPrice' => $prop->rentPrice ? (int) $prop->rentPrice : null,
            'lotSize' => $prop->lotSize ? (int) $prop->lotSize : null,
            'buildingSize' => $prop->buildingSize ? (int) $prop->buildingSize : null,
            'carCount' => $prop->carCount ? (int) $prop->carCount : null,
            'bedroomCount' => $prop->bedroomCount ? (int) $prop->bedroomCount : null,
            'bathroomCount' => $prop->bathroomCount ? (int) $prop->bathroomCount : null,
            'floorCount' => $prop->floorCount ? (int) $prop->floorCount : null,
            'electricPower' => $prop->electricPower ? (int) $prop->electricPower : null,
            'facing' => $prop->facing,
            'ownership' => $prop->ownership,
            'verifyStatus' => $prop->verifyStatus,
            'cityId' => $prop->cityId,
            'cityName' => $prop->cityName,
            'city' => $prop->city,
            'pictureUrls' => $prop->pictureUrls,
            'coordinate' => [
                'latitude' => $prop->latitude,
                'longitude' => $prop->longitude,
            ],
            'updatedAt' => $prop->updated_at ? $prop->updated_at->isoFormat('D MMMM YYYY') : null,
            'listings' => ListingResource::collection($prop->listings),
        ];
    }
}
