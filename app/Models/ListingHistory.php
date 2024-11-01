<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property string $listingId
 * @property string $actor
 * @property string $impersonator
 * @property array<string, string> $before
 * @property array<string, string> $after
 * @property string $changes
 * @property Carbon $timestamp
 */
class ListingHistory extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'listing_histories';

    protected $fillable = [
        'listingId',
        'actor',
        'impersonator',
        'before',
        'after',
        'changes',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    /**
     * Get the listing associated with the history.
     *
     * @return BelongsTo<Listing, ListingHistory>
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
