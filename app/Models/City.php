<?php

namespace App\Models;

use App\DTO\GeoJsonObject;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

/**
 * @property int $osmId
 * @property GeoJsonObject $location
 * @property string $name
 * @property string $provinceName
 */
class City extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'cities';

    /**
     * cast location to GeoJsonObject
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array<string>, array<string>>
     */
    protected function location(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $locationValue = [];

                if ($value instanceof BSONDocument || is_array($value)) {
                    $locationValue = $value instanceof BSONDocument ? $value->getArrayCopy() : $value;

                    if (isset($locationValue['coordinates']) && ($locationValue['coordinates'] instanceof BSONArray || is_array($locationValue['coordinates']))) {
                        $locationValue['coordinates'] = is_array($locationValue['coordinates']) ? $locationValue['coordinates'] : $locationValue['coordinates']->getArrayCopy();
                    }
                }

                if (isset($locationValue['coordinates'])) {
                    $coordinates = $locationValue['coordinates'];
                    $locationValue['coordinates']['longitude'] = isset($coordinates[0]) ? (float)$coordinates[0] : 0.0;
                    $locationValue['coordinates']['latitude'] = isset($coordinates[1]) ? (float)$coordinates[1] : 0.0;
                }

                return GeoJsonObject::from($locationValue);
            }
        );
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' - ' . $this->provinceName;
    }

    public function getLatitudeAttribute(): float
    {
        /** @var GeoJsonObject $location */
        $location = $this->location;
        return $location->coordinates->latitude ?? 0.0;
    }

    public function getLongitudeAttribute(): float
    {
        /** @var GeoJsonObject $location */
        $location = $this->location;
        return $location->coordinates->longitude ?? 0.0;
    }
}
