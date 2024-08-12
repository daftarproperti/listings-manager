<?php

namespace App\Models;

use App\Helpers\TelegramPhoto;
use App\Models\Traits\CityAttributeTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Eloquent\Casts\ObjectId;

/**
 * @property string $id
 * @property int $user_id
 * @property string $username
 * @property string $phoneNumber
 * @property string $accountType
 * @property string $email
 * @property string $password
 * @property string $name
 * @property string $city
 * @property int $cityId
 * @property string $description
 * @property string $picture
 * @property string $company
 * @property bool $isPublicProfile
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    /** @use CityAttributeTrait<User> */
    use CityAttributeTrait;

    protected $connection = 'mongodb';

    protected const INDIVIDUAL = "individual";
    protected const PROFESSIONAL = "professional";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phoneNumber',
        'username',
        'email',
        'password',
        'accountType',
        'name',
        'city',
        'cityId',
        'description',
        'picture',
        'company',
        'isPublicProfile'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        '_id' => ObjectId::class,
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function toListingUser(): ListingUser
    {
        $listingUser = new ListingUser();
        $listingUser->name = $this->name;
        $listingUser->userName = $this->username ?? null;
        $listingUser->userId = $this->user_id;
        $listingUser->source = 'app';

        return $listingUser;
    }

    public static function generateUserIdWithKey(string $userIdKey, string $phoneNumber): int
    {
        $hash = hash_hmac('sha256', $phoneNumber, $userIdKey, true);

        // Take the first 7 bytes (56 bits) of the hash and convert the 7-byte string to a 56-bit unsigned integer
        $hash_56bit = substr($hash, 0, 7);
        $userid = 0;
        for ($i = 0; $i < 7; $i++) {
            $userid = ($userid << 8) | ord($hash_56bit[$i]);
        }

        return $userid;
    }

    public static function generateUserId(string $phoneNumber): int
    {
        return self::generateUserIdWithKey(
            type(env('USER_ID_KEY') ?? 'default-key')->asString(),
            $phoneNumber,
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (!isset($model->user_id)) {
                $model->user_id = self::generateUserId($model->phoneNumber);
            }
        });
    }
}
