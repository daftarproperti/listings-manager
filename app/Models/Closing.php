<?php

namespace App\Models;

use App\Helpers\PhoneNumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property string $closingType
 * @property string $clientName
 * @property string $clientPhoneNumber
 * @property int $transactionValue
 * @property Carbon $date
 * @property Listing $listing
 * @property string $listing_id
 * @property string $status
 * @property string $commissionStatus
 * @property string $notes
 * @property string $id
 */
class Closing extends Model
{
    protected $collection = 'closings';
    protected $fillable = ['closingType', 'clientName', 'clientPhoneNumber', 'transactionValue', 'date'];

    /**
     * Retrieve the listing relationship.
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Listing, Closing>.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * @param string $value
     * @return void
     */
    public function setClientPhoneNumberAttribute($value): void
    {
        $this->attributes['clientPhoneNumber'] = PhoneNumber::canonicalize($value);
    }


}
