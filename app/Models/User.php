<?php

namespace App\Models;

use App\Models\Traits\CityAttributeTrait;
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (!isset($model->user_id)) {
                $model->user_id = random_int(1, PHP_INT_MAX);
            }
        });
    }
}
