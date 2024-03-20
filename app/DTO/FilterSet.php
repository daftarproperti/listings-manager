<?php

namespace App\DTO;

use Spatie\LaravelData\Dto;

class FilterMinMax extends Dto {
    public ?int $min;
    public ?int $max;
}

/**
 * Filter definition for listings and properties.
 */
class FilterSet extends Dto
{
    public ?int $userId;
    public ?string $q;
    public ?bool $collection;
    public int|FilterMinMax|null $price;
    public ?string $type;
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
