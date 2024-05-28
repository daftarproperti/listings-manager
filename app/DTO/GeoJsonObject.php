<?php

namespace App\DTO;

use App\Models\Coordinate;
use Spatie\LaravelData\Data;

class GeoJsonObject extends Data
{
    public string $type;
    public Coordinate $coordinates;
}
