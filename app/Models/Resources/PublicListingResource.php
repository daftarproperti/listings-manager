<?php

namespace App\Models\Resources;

use App\Models\Enums\VerifyStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PublicListing",
    type: "object"
)]
/**
 * Representation of a Listing which is available publicly.
 */
class PublicListingResource extends JsonResource
{
    #[OA\Property(property: "listingId", type: "integer")]
    #[OA\Property(property: "listingIdStr", type: "string")]
    #[OA\Property(property: "title", type: "string")]
    #[OA\Property(property: "propertyType", ref: "#/components/schemas/PropertyType")]
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
    #[OA\Property(property: "isVerified", type: "boolean")]
    #[OA\Property(property: "cityName", type: "string")]
    #[OA\Property(property: "cityId", type: "integer")]
    #[OA\Property(property: "pictureUrls", type: "array", items: new OA\Items(type: "string", format: "uri", example: "https://example.com/image.jpg"))]
    #[OA\Property(property: "coordinate", type: "object", properties: [
        new OA\Property(property: "latitude", type: "integer"),
        new OA\Property(property: "longitude", type: "integer")
    ])]
    #[OA\Property(property: "updatedAt", type: "string", format: "date-time")]

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\Listing $listing */
        $listing = $this->resource;

        return [
            'listingId' => $listing->listingId,
            'listingIdStr' => (string)$listing->listingId,
            'title' => $listing->title,
            'propertyType' => $listing->propertyType,
            'listingForSale' => $listing->listingForSale ?? false,
            'listingForRent' => $listing->listingForRent ?? false,
            'address' => $listing->address,
            'description' => $listing->description,
            'price' => $listing->price,
            'rentPrice' => $listing->rentPrice,
            'lotSize' => $listing->lotSize,
            'buildingSize' => $listing->buildingSize,
            'carCount' => $listing->carCount,
            'bedroomCount' => $listing->bedroomCount,
            'bathroomCount' => $listing->bathroomCount,
            'floorCount' => $listing->floorCount,
            'electricPower' => $listing->electricPower,
            'facing' => $listing->facing,
            'ownership' => $listing->ownership,
            'isVerified' => $listing->verifyStatus === VerifyStatus::APPROVED,
            'cityName' => $listing->cityName,
            'cityId' => $listing->cityId,
            'pictureUrls' => $listing->pictureUrls,
            'coordinate' => [
                'latitude' => $listing->coordinate->latitude,
                'longitude' => $listing->coordinate->longitude,
            ],
            'updatedAt' => $listing->updated_at->toIso8601String(),
        ];
    }
}
