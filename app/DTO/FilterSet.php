<?php

namespace App\DTO;

use Spatie\LaravelData\Dto;

/**
 * @OA\Schema(
 *     schema="FilterSet",
 *     type="object",
 *     description="Filter Set DTO",
 *     @OA\Property(property="userId", type="integer", nullable=true, description="User ID"),
 *     @OA\Property(property="q", type="string", nullable=true, description="Query"),
 *     @OA\Property(property="collection", type="boolean", nullable=true, description="Collection"),
 *     @OA\Property(property="price", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="propertyType", type="string", nullable=true, description="Property Type"),
 *     @OA\Property(property="bedroomCount", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="bathroomCount", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="lotSize", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="buildingSize", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="ownership", type="string", nullable=true, description="Ownership"),
 *     @OA\Property(property="carCount", ref="#/components/schemas/FilterMinMax"),
 *     @OA\Property(property="electricPower", type="integer", nullable=true, description="Electric Power"),
 *     @OA\Property(property="sort", type="string", nullable=true, description="Sort"),
 *     @OA\Property(property="order", type="string", nullable=true, description="Order"),
 *     @OA\Property(property="city", type="string", nullable=true, description="City"),
 * )
 */
class FilterSet extends Dto
{
    public ?int $userId;
    public ?string $q;
    public ?bool $collection;
    public int|FilterMinMax|null $price;
    public ?string $propertyType;
    public int|FilterMinMax|null $bedroomCount;
    public int|FilterMinMax|null $bathroomCount;
    public int|FilterMinMax|null $lotSize;
    public int|FilterMinMax|null $buildingSize;
    public ?string $ownership;
    public int|FilterMinMax|null $carCount;
    public ?int $electricPower;
    public ?string $sort;
    public ?string $order;
    public ?string $city;
}
