<?php

namespace App\Models;

use App\Helpers\Cast;
use App\Helpers\NumFormatter;
use App\Helpers\TelegramPhoto;
use App\Models\Enums\ActiveStatus;
use App\Models\Enums\VerifyStatus;
use App\Models\FacingDirection;
use App\Models\Traits\CityAttributeTrait;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;
use Google\Analytics\Data\V1alpha\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use MongoDB\BSON\UTCDateTime;

/**
 * @property string $id
 * @property ?int $strapiId Internal identifier to indicate whether this listing is imported from strapi DB.
 *                          If set, this listing was imported from strapi DB having that id.
 *                          This id is useful to prevent double import.
 *                          May be deleted once strapi migration is done completely.
 * @property int $listingId The unique canonical ID known by external
 * @property string $sourceText
 * @property string $title
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
 * @property int $viewCount
 * @property int $matchFilterCount
 * @property FacingDirection $facing
 * @property PropertyOwnership $ownership
 * @property VerifyStatus $verifyStatus
 * @property ActiveStatus $activeStatus
 * @property AdminNote $adminNote
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
 * @property TelegramUserProfile $user_profile
 * @property ListingUser|null $user
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class Listing extends Model
{
    const CACHE_LENGTH = 60 * 60 * 24;

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
        'description' => 'string'
    ];

    public function getMatchFilterCountAttribute(): int
    {
        if (!env('FEATURE_FILTER_COUNT_MATCHES')) {
            return 0;
        }

        return Cache::remember("listing-" . $this->id . "-matchFilterCount", self::CACHE_LENGTH, function () {
            return SavedSearch::countSavedSearchMatches($this->id);
        });
    }

    public function getViewCountAttribute(): int
    {
        // Temporarily disable calling Google Analytics API since it turns out to be blocking very long.
        // TODO: Optimize this by not directly fetching here, but dispatching the fetch job in a queue.
        // Re-enable the function below when this is done.
        return 0;
    }

    public function disabled_getViewCountAttribute(): int
    {
        if (!env('PHASE1')) {
            return 0;
        }

        try {
            $startDate = Carbon::createFromDate(2024, 1, 1);
            $endDate = Carbon::now();
            $periode = Period::create($startDate, $endDate);

            $metrics = ['eventCount'];
            $dimension = ['customEvent:listing_id'];
            $dimensionFilter = new FilterExpression([
                'filter' => new Filter([
                    'field_name' => 'customEvent:listing_id',
                    'string_filter' => new StringFilter([
                        'match_type' => MatchType::EXACT,
                        'value' => $this->id,
                    ]),
                ]),
            ]);

            $analyticsData = Analytics::get($periode, $metrics, $dimension, 10, [], 0, $dimensionFilter)->first();

            if (is_array($analyticsData)) {
                return (int) $analyticsData['eventCount'];
            } else {
                return 0;
            }
        } catch (Exception $e) {
            // Logging the error here. The failure can occur for a couple of reasons:
            // 1. The 'property_id' doesn't exist or is invalid.
            // 2. Missing or incorrect Google Analytics service account credentials.
            Log::error($e);
            return 0;
        }
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

    public function getUserProfileAttribute(): ?TelegramUserProfile
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

                $profile = new TelegramUserProfile();

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
                    return TelegramPhoto::reformatPictureUrlsIntoGcsUrls($value);
                } else {
                    return [];
                }
            }
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
                $pictureUrls[] = TelegramPhoto::getFileNameFromUrl($url);
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
     * @return Attribute<?AdminNote, AdminNote>
     * modifying date format in adminNote input, from Carbon\Carbon to MongoDB\BSON\UTCDateTime
     */
    protected function adminNote(): Attribute
    {
        return Attribute::make(
            get: function (mixed $val): ?AdminNote {
                if (!is_array($val)) return null;

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
            }
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
            case "propertyType":
            case "listingType":
                return $value ? strtolower(Cast::toString($value)) : "unknown";
            case "ownership":
                return PropertyOwnership::sanitize(Cast::toString($value));
            case "facing":
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

    protected static function boot() {
        parent::boot();

        static::creating(function ($listing) {
            /** @var User $user */
            $user = Auth::user();
            if (env('MAX_LISTINGS_PER_USER')) {
                $maxListings = (int)type(env('MAX_LISTINGS_PER_USER'))->asString();
                $count = Listing::where('user.userId', $user->user_id)->count();

                if ($count >= $maxListings) {
                    throw new \Exception("Untuk sementara batas maksimum listing setiap user adalah $maxListings.");
                }
            }

            $listing->verifyStatus = VerifyStatus::ON_REVIEW;
            $listing->listingId = random_int(1, PHP_INT_MAX);
        });
    }
}
