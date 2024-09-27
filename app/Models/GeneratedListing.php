<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Class GeneratedListing
 *
 * @property string $job_id
 * @property array $generated_listing
 */
class GeneratedListing extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'generated_listings';

    protected $fillable = [
        'job_id',
        'generated_listing',
    ];

    protected $casts = [
        // TODO: Change into more strong typed Listing model
        'generated_listing' => 'array',
    ];

    /**
    * Get the generated listing attribute.
    *
    * @param string|null $value
    * @return mixed
    */
    public function getGeneratedListingAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        return json_decode($value, true);
    }
}
