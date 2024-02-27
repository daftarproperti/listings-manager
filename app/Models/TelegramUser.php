<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property TelegramUserProfile|null $profile
 */
class TelegramUser extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'telegram_users';
    protected $fillable = ['user_id', 'first_name', 'last_name', 'username'];

    protected $casts = [
        'profile' => TelegramUserProfile::class,
    ];
}
