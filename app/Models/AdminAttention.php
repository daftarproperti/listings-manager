<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property int $listingId
 * @property Carbon $listingUpdatedAt
 */
class AdminAttention extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'admin_attentions';

    protected $fillable = [
        'listingId',
        'listingUpdatedAt',
    ];

    protected $casts = [
        'listingUpdatedAt' => 'datetime',
    ];

    /**
     * Get the listing associated with the history.
     *
     * @return BelongsTo<Listing, AdminAttention>
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Set the listingUpdatedAt attribute.
     *
     * @param Carbon $value
     */
    public function setListingUpdatedAtAttribute($value): void
    {
        $this->attributes['listingUpdatedAt'] = new UTCDateTime($value->getTimestampMs());
    }

    /**
     * Get the listingUpdatedAt attribute.
     *
     * @param UTCDateTime $value
     * @return Carbon
     */
    public function getListingUpdatedAtAttribute($value): Carbon
    {
        return Carbon::createFromTimestamp($value->toDateTime()->getTimestamp());
    }
}
