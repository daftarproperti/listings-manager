<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TelegramUser extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'telegram_users';
    protected $fillable = ['user_id', 'first_name', 'last_name', 'username'];

}
