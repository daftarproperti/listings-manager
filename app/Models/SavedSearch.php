<?php

namespace App\Models;

use App\DTO\FilterSet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

/**
 * @property int $userId
 * @property string $id
 * @property string $title
 * @property FilterSet $filterSet
 */
class SavedSearch extends Model
{
    use HasFactory,
        SoftDeletes;

    protected $fillable = [
        'userId',
        'title',
        'filterSet',
    ];
}
