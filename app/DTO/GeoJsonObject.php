<?php

namespace App\DTO;

use App\Models\Coordinate;
use Spatie\LaravelData\Data;

class GeoJsonObject extends Data
{
    public string $type;
    public Coordinate $coordinates;

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'coordinates' => [
                $this->coordinates->longitude,
                $this->coordinates->latitude,
            ]
        ];
    }
}
