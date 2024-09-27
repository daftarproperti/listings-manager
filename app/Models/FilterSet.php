<?php

namespace App\Models;

use Spatie\LaravelData\Data;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "FilterSet",
    type: "object",
    description: "Filter Set"
)]
class FilterSet extends Data
{
    #[OA\Property(property: "userId", type: "integer", nullable: true, description: "User ID")]
    public ?int $userId = null;

    #[OA\Property(property: "q", type: "string", nullable: true, description: "Query")]
    public ?string $q = null;

    #[OA\Property(property: "collection", type: "boolean", nullable: true, description: "Collection")]
    public ?bool $collection = null;

    #[OA\Property(property: "price", ref: "#/components/schemas/FilterMinMax")]
    public int|FilterMinMax|null $price = null;

    #[OA\Property(property: "rentPrice", ref: "#/components/schemas/FilterMinMax")]
    public int|FilterMinMax|null $rentPrice = null;

    #[OA\Property(property: "propertyType", ref: "#/components/schemas/PropertyType")]
    public ?PropertyType $propertyType = null;

    #[OA\Property(property: "listingType", ref: "#/components/schemas/ListingType")]
    public ?ListingType $listingType = null;

    #[OA\Property(property: "listingForSale", type: "boolean", nullable: true)]
    public ?bool $listingForSale = null;

    #[OA\Property(property: "listingForRent", type: "boolean", nullable: true)]
    public ?bool $listingForRent = null;

    #[OA\Property(property: "bedroomCount", ref: "#/components/schemas/FilterMinMax")]
    public int|FilterMinMax|null $bedroomCount = null;

    #[OA\Property(property: "bathroomCount", ref: "#/components/schemas/FilterMinMax")]
    public int|FilterMinMax|null $bathroomCount = null;

    #[OA\Property(property: "lotSize", ref: "#/components/schemas/FilterMinMax")]
    public int|FilterMinMax|null $lotSize = null;

    #[OA\Property(property: "buildingSize", ref: "#/components/schemas/FilterMinMax")]
    public int|FilterMinMax|null $buildingSize = null;

    #[OA\Property(property: "facing", ref: "#/components/schemas/FacingDirection")]
    public ?FacingDirection $facing = null;

    #[OA\Property(property: "ownership", ref: "#/components/schemas/PropertyOwnership")]
    public ?PropertyOwnership $ownership = null;

    #[OA\Property(property: "carCount", ref: "#/components/schemas/FilterMinMax")]
    public int|FilterMinMax|null $carCount = null;

    #[OA\Property(property: "floorCount", type: "integer", nullable: true, description: "Floor Count")]
    public ?int $floorCount = null;

    #[OA\Property(property: "electricPower", type: "integer", nullable: true, description: "Electric Power")]
    public ?int $electricPower = null;

    #[OA\Property(property: "sort", type: "string", nullable: true, description: "Sort")]
    public ?string $sort = null;

    #[OA\Property(property: "order", type: "string", nullable: true, description: "Order")]
    public ?string $order = null;

    #[OA\Property(property: "city", type: "string", nullable: true, description: "City")]
    public ?string $city = null;

    #[OA\Property(property: "cityId", type: "integer", nullable: true, description: "City (OSM) ID")]
    public ?int $cityId = null;
}
