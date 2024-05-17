<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property string $firstName
 * @property string $lastName
 * @property string $username
 * @property string $phoneNumber
 * @property string $accountType
 * @property string $email
 * @property string $password
 * @property UserProfile|null $profile
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mongodb';

    protected $primaryKey = 'phoneNumber';

    protected const INDIVIDUAL = "individual";
    protected const PROFESSIONAL = "professional";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'username',
        'phoneNumber',
        'accountType',
        'email',
        'password',
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
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'profile' => AttributeCaster::class.':'.UserProfile::class,
    ];

    public function getAuthIdentifier()
    {
        return $this->phoneNumber;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}
