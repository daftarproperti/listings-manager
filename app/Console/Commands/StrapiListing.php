<?php

namespace App\Console\Commands;

use Spatie\LaravelData\Data;

class StrapiListing extends Data
{
    public ?int $id = null;
    public ?string $title = null;
    public ?string $address = null;
    public ?string $description = null;
    public ?float $price = null;
    public ?float $lot_size = null;
    public ?float $building_size = null;
    public ?string $facing = null;
    public ?int $floor_count = null;
    public ?int $bedroom_count = null;
    public ?int $bathroom_count = null;
    public ?int $car_count = null;
    public ?string $ownership = null;
    public ?string $condition = null;
    public ?string $seller_name = null;
    public ?string $seller_phone = null;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public ?string $place_id = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?string $published_at = null;
    public ?string $created_by_id = null;
    public ?string $updated_by_id = null;
    public ?string $picture_url = null;
    public ?int $osm_id = null;
}
