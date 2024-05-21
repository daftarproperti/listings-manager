<?php

namespace App\Models;

use Spatie\LaravelData\Data;

class Coordinate extends Data
{
    public ?float $latitude = null;
    public ?float $longitude = null;
}
