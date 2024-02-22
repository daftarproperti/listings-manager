<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property int $update_id
 * @property string $message
 */
class RawMessage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'raw_messages';
}
