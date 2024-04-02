<?php

namespace App\Models;

use App\DTO\Telegram\Message;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $update_id
 * @property Message $message
 */
class RawMessage extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'raw_messages';

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Message,Message>
    */
    protected function message(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return Message::from($value);
            }
        );
    }
}
