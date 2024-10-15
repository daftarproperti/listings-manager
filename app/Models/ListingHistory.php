<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $id
 * @property array<string, string> $before
 * @property array<string, string> $after
 * @property array<string, string> $changes
 * @property Carbon $timestamp
 */
class ListingHistory extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'listing_histories';

    protected $fillable = [
        'listingId',
        'before',
        'after',
        'changes',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'changes' => 'array',
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
