<?php

namespace App\Models;

use App\Helpers\TelegramPhoto;
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
 * @property string $address
 * @property string $description
 * @property string $price
 * @property string $lotSize
 * @property string $buildingSize
 * @property string $carCount
 * @property string $bedroomCount
 * @property string $bathroomCount
 * @property string $floorCount
 * @property string $electricPower
 * @property FacingDirection $facing
 * @property PropertyOwnership $ownership
 * @property string $city
 * @property array<string> $pictureUrls
 * @property double $latitude
 * @property double $longitude
 * @property bool $user_can_edit
 * @property bool $isPrivate
 * @property TelegramUserProfile $user_profile
 * @property PropertyUser|null $user
 */
class Property extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'properties';

    protected $casts = [
        'propertyType' => PropertyType::class,
        'listingType' => ListingType::class,
        'user' => AttributeCaster::class.':'.PropertyUser::class,
        'ownership' => PropertyOwnership::class,
        'facing' => FacingDirection::class,
        'buildingSize' => 'int',
        'bedroomCount' => 'int',
        'bathroomCount' => 'int',
        'lotSize' => 'int',
        'carCount' => 'int',
        'floorCount' => 'int',
        'electricPower' => 'int',
        'price' => 'float',
    ];


    public function getUserCanEditAttribute(): bool
    {
        $currentUserId = app(TelegramUser::class)->user_id ?? null;

        if (!$currentUserId) {
            return false;
        }

        $propertyUser = (object) $this->user;

        return $currentUserId == ($propertyUser->userId ?? null);
    }

    public function getUserProfileAttribute(): ?TelegramUserProfile
    {
        $user = $this->user ? (object) $this->user : null;
        $userSource = $user ? ($user->source ?? null) : null;

        if (!$userSource || !$user) {
            return null;
        }

        switch($userSource) {
            case 'telegram':
                /** @var TelegramUser $teleUser */
                $teleUser = TelegramUser::where('user_id', $user->userId)->first();
                return $teleUser->profile;
            default:
                return null;
        }
    }

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
