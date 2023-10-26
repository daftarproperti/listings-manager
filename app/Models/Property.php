<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Property extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'properties';
}
