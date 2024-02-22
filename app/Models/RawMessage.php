<?php

namespace App\Models;

use App\DTO\Telegram\Message;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property int $update_id
 * @property Message $message
 */
class RawMessage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'raw_messages';
}
