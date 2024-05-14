<?php

namespace App\Models;

use App\Models\FacingDirection;
use App\Models\ListingType;
use App\Models\PropertyOwnership;
use App\Models\PropertyType;
use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="FilterSet",
 *     type="object",
 *     description="Filter Set",
 *     @OA\Property(property="userId", type="integer", nullable=true, description="User ID"),
 *     @OA\Property(property="q", type="string", nullable=true, description="Query"),
 *     @OA\Property(property="collection", type="boolean", nullable=true, description="Collection"),
 *     @OA\Property(property="price", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="propertyType", ref="#/components/schemas/PropertyType"),
 *     @OA\Property(property="listingType", ref="#/components/schemas/ListingType"),
 *     @OA\Property(property="bedroomCount", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="bathroomCount", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="lotSize", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="buildingSize", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="facing", ref="#/components/schemas/FacingDirection"),
 *     @OA\Property(property="ownership", ref="#/components/schemas/PropertyOwnership"),
 *     @OA\Property(property="carCount", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="floorCount", type="integer", nullable=true, description="Floor Count"),
 *     @OA\Property(property="electricPower", type="integer", nullable=true, description="Electric Power"),
 *     @OA\Property(property="sort", type="string", nullable=true, description="Sort"),
 *     @OA\Property(property="order", type="string", nullable=true, description="Order"),
 *     @OA\Property(property="city", type="string", nullable=true, description="City"),
 * )
 */
class FilterSet extends Data
{
    public ?int $userId = null;
    public ?string $q = null;
    public ?bool $collection = null;
    public int|FilterMinMax|null $price = null;
    public ?PropertyType $propertyType = null;
    public ?ListingType $listingType = null;
    public int|FilterMinMax|null $bedroomCount = null;
    public int|FilterMinMax|null $bathroomCount = null;
    public int|FilterMinMax|null $lotSize = null;
    public int|FilterMinMax|null $buildingSize = null;
    public ?FacingDirection $facing = null;
    public ?PropertyOwnership $ownership = null;
    public int|FilterMinMax|null $carCount = null;
    public ?int $floorCount = null;
    public ?int $electricPower = null;
    public ?string $sort = null;
    public ?string $order = null;
    public ?string $city = null;
}
