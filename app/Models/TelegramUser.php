<?php

namespace App\Models;

use App\Models\Traits\CityAttributeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property TelegramUserProfile|null $profile
 */
class TelegramUser extends Model
{
    use HasFactory;

    /** @use CityAttributeTrait<TelegramUser> */
    use CityAttributeTrait;

    protected $connection = 'mongodb';
    protected $collection = 'telegram_users';
    protected $fillable = ['user_id', 'first_name', 'last_name', 'username'];

    protected $casts = [
        'profile' => AttributeCaster::class.':'.TelegramUserProfile::class,
    ];

    public function toListingUser(): ListingUser
    {
        $listingUser = new ListingUser();
        $listingUser->name = $this->first_name . " " . $this->last_name;
        $listingUser->userName = $this->username;
        $listingUser->userId = $this->user_id;
        $listingUser->source = 'telegram';

        return $listingUser;
    }
}
