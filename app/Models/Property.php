<?php

namespace App\Models;

use App\Helpers\TelegramPhoto;
use App\Models\Enums\VerifyStatus;
use App\Models\Traits\CityAttributeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property string $id
 * @property string $sourceText
 * @property string $title
 * @property PropertyType $propertyType
 * @property ListingType $listingType
 * @property bool $listingForSale
 * @property bool $listingForRent
 * @property string $address
 * @property string $description
 * @property int $price
 * @property int $rentPrice
 * @property int $lotSize
 * @property int $buildingSize
 * @property int $carCount
 * @property int $bedroomCount
 * @property int $bathroomCount
 * @property int $floorCount
 * @property int $electricPower
 * @property FacingDirection $facing
 * @property PropertyOwnership $ownership
 * @property VerifyStatus $verifyStatus
 * @property string $city
 * @property int $cityId
 * @property array<string> $pictureUrls
 * @property double $latitude
 * @property double $longitude
 * @property bool $isPrivate
 */
class Property extends Model
{
    use SoftDeletes;
    use HasFactory;

    /** @use CityAttributeTrait<Property> */
    use CityAttributeTrait;

    protected $connection = 'mongodb';
    protected $collection = 'properties';

    protected $casts = [
        'propertyType' => PropertyType::class,
        'listingType' => ListingType::class,
        'listingForSale' => 'boolean',
        'listingForRent' => 'boolean',
        'ownership' => PropertyOwnership::class,
        'facing' => FacingDirection::class,
        'verifyStatus' => VerifyStatus::class,
        'buildingSize' => 'int',
        'bedroomCount' => 'int',
        'bathroomCount' => 'int',
        'lotSize' => 'int',
        'carCount' => 'int',
        'floorCount' => 'int',
        'electricPower' => 'int',
        'price' => 'int',
        'rentPrice' => 'int',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array<string>, array<string>>
    */
    protected function pictureUrls(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    return TelegramPhoto::reformatPictureUrlsIntoGcsUrls($value);
                } else {
                    return [];
                }
            }
        );
    }

     /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<array<string>, array<string>>
    */
    protected function listings(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    return Listing::find($value);
                } else {
                    return new Collection();
                }
            }
        );
    }

    /**
     * Overrides Illuminate\Database\Eloquent\Concerns\HasAttributes::getEnumCaseFromValue;
     *
     * To always sanitize the enum value when getting it out from DB.
     *
     * @param  string  $enumClass
     * @param  string|int  $value
     * @return \UnitEnum|\BackedEnum
     */
    protected function getEnumCaseFromValue($enumClass, $value)
    {
        $sanitized = $value;
        if (method_exists($enumClass, 'sanitize')) {
            $sanitized = $enumClass::sanitize($value);
        }

        return parent::getEnumCaseFromValue($enumClass, $sanitized);
    }
}
