<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 */
class TelegramUser extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'telegram_users';
    protected $fillable = ['user_id', 'first_name', 'last_name', 'username'];

}
