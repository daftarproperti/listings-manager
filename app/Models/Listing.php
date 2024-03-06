<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $title
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
    ];

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

        switch($userSource) {
            case 'telegram':
                $teleUser = TelegramUser::where('user_id', $user->userId)->first();
                $profile = $teleUser->profile ?? null;
                return $profile ? new TelegramUserProfile((array) $profile) : null;
            default:
                return null;
        }
    }
}