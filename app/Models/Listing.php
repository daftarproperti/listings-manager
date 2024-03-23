<?php

namespace App\Models;

use App\Helpers\NumFormatter;
use App\Helpers\TelegramPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property string $id
 * @property string $title
 * @property string $address
 * @property string $description
 * @property string $formatted_price
 * @property float $price
 * @property int $lotSize
 * @property int $buildingSize
 * @property int $carCount
 * @property int $bedroomCount
 * @property int $bathroomCount
 * @property int $floorCount
 * @property int $electricPower
 * @property string $facing
 * @property string $ownership
 * @property string $city
 * @property array<string> $pictureUrls
 * @property double $latitude
 * @property double $longitude
 * @property array<string, string> $contacts
 * @property bool $user_can_edit
 * @property bool $isPrivate
 * @property TelegramUserProfile $user_profile
 * @property ListingUser|null $user
 */
class Listing extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'listings';

    protected $casts = [
        'user' => ListingUser::class,
        'buildingSize' => 'int',
        'bedroomCount' => 'int',
        'bathroomCount' => 'int',
        'lotSize' => 'int',
        'carCount' => 'int',
        'floorCount' => 'int',
        'electricPower' => 'int',
        'price' => 'float',
    ];

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
                $teleUser = TelegramUser::where('user_id', $user->userId)->first();
                $profile = $teleUser->profile ?? null;
                return $profile ? new TelegramUserProfile((array) $profile) : null;
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
}
