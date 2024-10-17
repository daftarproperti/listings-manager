<?php

namespace App\Models;

use App\DTO\GeoJsonObject;
use App\Helpers\Cast;
use App\Helpers\NumFormatter;
use App\Helpers\Photo;
use App\Models\Enums\ActiveStatus;
use App\Models\Enums\VerifyStatus;
use App\Models\Traits\CityAttributeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\UTCDateTime;

/**
 * @property string $id
 * @property ?int $strapiId Internal identifier to indicate whether this listing is imported from strapi DB.
 *                          If set, this listing was imported from strapi DB having that id.
 *                          This id is useful to prevent double import.
 *                          May be deleted once strapi migration is done completely.
 * @property int $listingId The unique canonical ID known by external
 * @property string $sourceText
 * @property PropertyType $propertyType
 * @property ListingType $listingType
 * @property bool $listingForSale
 * @property bool $listingForRent
 * @property string $address
 * @property string $description
 * @property string $formatted_price
 * @property int $price
 * @property int $rentPrice
 * @property int $lotSize
 * @property int $buildingSize
 * @property int $carCount
 * @property int $bedroomCount
 * @property int $additionalBedroomCount
 * @property int $bathroomCount
 * @property int $additionalBathroomCount
 * @property int $floorCount
 * @property int $electricPower
 * @property FacingDirection $facing
 * @property PropertyOwnership $ownership
 * @property VerifyStatus $verifyStatus
 * @property ActiveStatus $activeStatus
 * @property AdminNote $adminNote
 * @property CancellationNote $cancellationNote
 * @property array<Closing> $closings
 * @property int $cityId
 * @property string $city
 * @property array<string> $pictureUrls
 * @property Coordinate $coordinate
 * @property array<string, string> $contact
 * @property bool $user_can_edit
 * @property bool $isPrivate
 * @property bool $withRewardAgreement
 * @property bool $isMultipleUnits
 * @property UserProfile $user_profile
 * @property ListingUser|null $user
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $expiredAt
 * @property \Illuminate\Support\Collection<int, AdminAttention>|null $adminAttentions
 * @property GeoJsonObject $indexedCoordinate
 */
class Listing extends Model
{
    use SoftDeletes;
    use HasFactory;
    /** @use CityAttributeTrait<Listing> */
    use CityAttributeTrait;

    protected $connection = 'mongodb';
    protected $collection = 'listings';

    protected $casts = [
        'listingId' => 'int',
        'propertyType' => PropertyType::class,
        'listingType' => ListingType::class,
        'listingForSale' => 'boolean',
        'listingForRent' => 'boolean',
        'user' => AttributeCaster::class . ':' . ListingUser::class,
        'coordinate' => AttributeCaster::class . ':' . Coordinate::class,
        'ownership' => PropertyOwnership::class,
        'facing' => FacingDirection::class,
        'verifyStatus' => VerifyStatus::class,
        'activeStatus' => ActiveStatus::class,
        'buildingSize' => 'int',
        'bedroomCount' => 'int',
        'additionalBedroomCount' => 'int',
        'bathroomCount' => 'int',
        'additionalBathroomCount' => 'int',
        'lotSize' => 'int',
        'carCount' => 'int',
        'floorCount' => 'int',
        'electricPower' => 'int',
        'price' => 'int',
        'rentPrice' => 'int',
        'cityId' => 'int',
        'address' => 'string',
        'description' => 'string',
        'expiredAt' => 'datetime',
    ];

    /**
     * Get all the listing histories for this listing.
     *
     * @return HasMany<ListingHistory>
     */
    public function listingHistories()
    {
        return $this->hasMany(ListingHistory::class, 'listingId');
    }

    /**
     * Get all admin attentions for this listing
     *
     * @return HasMany<AdminAttention>
     */
    public function adminAttentions()
    {
        return $this->hasMany(AdminAttention::class, 'listingId');
    }

    /**
     * Retrieve the closings relationship.
     *  @return \Illuminate\Database\Eloquent\Relations\HasMany<Closing>.
     */
    public function closings(): HasMany
    {
        return $this->hasMany(Closing::class);
    }

    /**
     * Retrieve the aiReview relationship.
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne<AiReview>.
     */
    public function aiReview(): HasOne
    {
        return $this->hasOne(AiReview::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return NumFormatter::numberFormatShort($this->price);
    }

    public function getUserCanEditAttribute(): bool
    {
        $currentUserId = Auth::user()->user_id ?? null;

        if (!$currentUserId) {
            return false;
        }

        $listingUser = (object) $this->user;

        return $currentUserId == ($listingUser->userId ?? null);
    }

    public function getUserProfileAttribute(): ?UserProfile
    {
        $user = $this->user;
        $userSource = $user?->source;

        if (!$userSource) {
            return null;
        }

        switch ($userSource) {
            case 'app':
                /** @var User|null $appUser */
                $appUser = User::where('user_id', $user->userId)->first();
                if (!$appUser) {
                    return null;
                }

                $profile = new UserProfile();

                $profile->name = $appUser->name;
                $profile->phoneNumber = $appUser->phoneNumber;
                $profile->city = $appUser->city;
                $profile->cityId = $appUser->cityId;
                $profile->cityName = $appUser->cityName;
                $profile->description = $appUser->description;
                $profile->company = $appUser->company;
                $profile->picture = $appUser->picture;
                $profile->isPublicProfile = $appUser->isPublicProfile;

                return $profile;
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
                    return Photo::reformatPictureUrlsIntoGcsUrls($value);
                } else {
                    return [];
                }
            },
        );
    }


    /**
     * put fileName only into DB
     * @return void
     */
    public function setPictureUrlsAttribute(mixed $value): void
    {
        //to handle if someone still using old convention
        $pictureUrls = [];

        if (is_array($value)) {
            foreach ($value as $url) {
                $pictureUrls[] = Photo::getFileNameFromUrl($url);
            }
        }

        $this->attributes['pictureUrls'] = $pictureUrls;
    }

    /**
     * force set city if listing city configuration is filled
     * @param string $value
     * @return void
     */

    public function setCityAttribute($value)
    {
        $listingCity = config('services.default_listing_city');

        if (empty($value) && !empty($listingCity)) {
            $value = $listingCity;
        }

        $this->attributes['city'] = $value;
    }

    /**
     * convert geo json object to array format
     * @param GeoJsonObject $value
     * @return void
     */
    public function setIndexedCoordinateAttribute(GeoJsonObject $value)
    {
        $this->attributes['indexedCoordinate'] = $value->toArray();
    }

    /**
     * @return Attribute<?AdminNote, AdminNote>
     * modifying date format in adminNote input, from Carbon\Carbon to MongoDB\BSON\UTCDateTime
     */
    protected function adminNote(): Attribute
    {
        return Attribute::make(
            get: function (mixed $val): ?AdminNote {
                if (!is_array($val)) {
                    return null;
                }

                if (isset($val['date'])) {
                    $val['date'] = Carbon::createFromTimestamp($val['date']->toDateTime()->getTimestamp());
                }

                return AdminNote::from($val);
            },
            // from generic Carbon to mongo-specific UTCDateTime.
            set: function (AdminNote $val) {
                $obj = (object)(array)$val;
                $obj->date = new UTCDateTime($val->date->getTimestampMs());
                return $obj;
            },
        );
    }

    /**
     * @return Attribute<?CancellationNote, CancellationNote>
     */
    protected function cancellationNote(): Attribute
    {
        return Attribute::make(
            get: function (mixed $val): ?CancellationNote {
                if (!is_array($val)) {
                    return null;
                }
                return CancellationNote::from($val);
            },
            set: function (CancellationNote $val) {
                $obj = (object)(array)$val;
                return $obj;
            },
        );
    }


    // Cast every attribute to the right type before going into DB.
    public function setAttribute($key, $value)
    {
        if ($this->hasCast($key)) {
            $value = $this->castAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    // Sanitize input before going in to DB or out from DB.
    public static function sanitizeField(string $key, mixed $value): mixed
    {
        // TODO: Rather then repeating similar cases below, this can be generalized to $enumClass::sanitize()
        switch ($key) {
            case 'propertyType':
            case 'listingType':
                return $value ? strtolower(Cast::toString($value)) : 'unknown';
            case 'ownership':
                return PropertyOwnership::sanitize(Cast::toString($value));
            case 'facing':
                return FacingDirection::sanitize(Cast::toString($value));
            default:
                return $value;
        }
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
