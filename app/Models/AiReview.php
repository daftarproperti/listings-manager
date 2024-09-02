<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property string $id
 * @property Listing $listing
 * @property array $results
 * @property string $status
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class AiReview extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ai_reviews';
    protected $fillable = [
        'results',
        'status',
    ];

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Listing, AiReview>.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
