<?php

namespace App\Models;

use App\Helpers\Cast;
use App\Helpers\NumFormatter;
use App\Helpers\TelegramPhoto;
use App\Models\Enums\VerifyStatus;
use App\Models\FacingDirection;
use Carbon\Carbon;
use Exception;
use Google\Analytics\Data\V1alpha\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

/**
 * @property string $id
 * @property ?int $strapiId Internal identifier to indicate whether this listing is imported from strapi DB.
 *                          If set, this listing was imported from strapi DB having that id.
 *                          This id is useful to prevent double import.
 *                          May be deleted once strapi migration is done completely.
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
 * @property int $bathroomCount
 * @property int $floorCount
 * @property int $electricPower
 * @property int $viewCount
 * @property int $matchFilterCount
 * @property FacingDirection $facing
 * @property PropertyOwnership $ownership
 * @property VerifyStatus $verifyStatus
 * @property string $city
 * @property array<string> $pictureUrls
 * @property double $latitude
 * @property double $longitude
 * @property array<string, string> $contact
 * @property bool $user_can_edit
 * @property bool $isPrivate
 * @property TelegramUserProfile $user_profile
 * @property ListingUser|null $user
 */
class Listing extends Model
{
    const CACHE_LENGTH = 60 * 60 * 24;

    use SoftDeletes;
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'listings';

    protected $casts = [
        'propertyType' => PropertyType::class,
        'listingType' => ListingType::class,
        'user' => AttributeCaster::class.':'.ListingUser::class,
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
    ];

    public function getMatchFilterCountAttribute(): int
    {
        if (!env('PHASE1')) {
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
        $currentUserId = app(TelegramUser::class)->user_id ?? null;

        if (!$currentUserId) {
            return false;
        }

        $listingUser = (object) $this->user;

        return $currentUserId == ($listingUser->userId ?? null);
    }

    public function getUserProfileAttribute(): ?TelegramUserProfile
    {
        $user = $this->user ? (object) $this->user : null;
        $userSource = $user ? ($user->source ?? null) : null;

        if (!$userSource || !$user) {
            return null;
        }

        switch ($userSource) {
            case 'telegram':
                /** @var TelegramUser|null $teleUser */
                $teleUser = TelegramUser::where('user_id', $user->userId)->first();
                return $teleUser?->profile;
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
}
