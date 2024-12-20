<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property string $id
 * @property Listing $listing
 * @property array<string> $results
 * @property string $status
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property array<string> $streetViewImages
 * @property array<string> $verifiedImageUrls
 */
class AiReview extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ai_reviews';
    protected $fillable = [
        'results',
        'status',
        'streetViewImages',
        'verifiedImageUrls',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Listing, AiReview>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Accessor for streetViewImages attribute.
     *
     * @param array<string>|null $value
     * @return array<string>
     */
    public function getStreetViewImagesAttribute(?array $value): array
    {
        $images = $value ?? [];
        $apiKey = type(config('services.google.maps_api_key'))->asString();

        // Filter to ensure all values are strings and append API key.
        return array_map(
            fn ($image) => $image . '&key=' . $apiKey,
            $images,
        );
    }
}
