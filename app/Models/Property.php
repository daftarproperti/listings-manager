<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'properties';

    public function getUserCanEditAttribute()
    {
        $currentUserId = app(TelegramUser::class)->user_id ?? null;

        if (!$currentUserId) {
            return false;
        }

        $propertyUser = $this->user;

        return $currentUserId === ($propertyUser['userId'] ?? null);
    }
}
