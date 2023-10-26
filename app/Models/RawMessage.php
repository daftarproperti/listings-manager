<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RawMessage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'raw_messages';
}
