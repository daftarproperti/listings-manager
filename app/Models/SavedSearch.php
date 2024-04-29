<?php

namespace App\Models;

use App\DTO\FilterSet;
use App\Exceptions\FilterMismatchException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
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

    public static function countSavedSearchMatches(mixed $listingId): int
    {
        $listing = Listing::where('_id', $listingId)->first();
        if (!$listing) {
            return 0;
        }

        $filters = SavedSearch::all();

        $matchingCount = 0;
        foreach ($filters as $filter) {
            try {
                // Check for exact matches on simple fields
                self::checkExactMatch($listing->city, $filter->filterSet['city'] ?? null);
                self::checkExactMatch($listing->floorCount, $filter->filterSet['floorCount'] ?? null);
                self::checkExactMatch($listing->electricPower, $filter->filterSet['electricPower'] ?? null);

                // Check for range matches on various fields
                self::checkMinMaxMatch($listing->price, $filter->filterSet['price']['min'] ?? null, $filter->filterSet['price']['max'] ?? null);
                self::checkMinMaxMatch($listing->bedroomCount, $filter->filterSet['bedroomCount']['min'] ?? null, $filter->filterSet['bedroomCount']['max'] ?? null);
                self::checkMinMaxMatch($listing->bathroomCount, $filter->filterSet['bathroomCount']['min'] ?? null, $filter->filterSet['bathroomCount']['max'] ?? null);
                self::checkMinMaxMatch($listing->lotSize, $filter->filterSet['lotSize']['min'] ?? null, $filter->filterSet['lotSize']['max'] ?? null);
                self::checkMinMaxMatch($listing->buildingSize, $filter->filterSet['buildingSize']['min'] ?? null, $filter->filterSet['buildingSize']['max'] ?? null);
                self::checkMinMaxMatch($listing->carCount, $filter->filterSet['carCount']['min'] ?? null, $filter->filterSet['carCount']['max'] ?? null);


                // Check for enum matches
                self::checkEnumMatch($listing->propertyType, $filter->filterSet['propertyType'] ?? null, PropertyType::class);
                self::checkEnumMatch($listing->propertyType, $filter->filterSet['propertyType'] ?? null, ListingType::class);
                self::checkEnumMatch($listing->ownership, $filter->filterSet['ownership'] ?? null, PropertyOwnership::class);
                self::checkEnumMatch($listing->facing, $filter->filterSet['facing'] ?? null, FacingDirection::class);
            } catch (FilterMismatchException $e) {
                Log::info($e);
                // If mismatch, skip to the next filter
                continue;
            }
            // All checks passed for this listing
            $matchingCount++;
        }
        return $matchingCount;
    }

    private static function checkExactMatch(mixed $listingValue, mixed $filterValue): void
    {
        if (!empty($filterValue) && $listingValue !== $filterValue) {
            throw new FilterMismatchException("Exact match failed for value: " . $listingValue);
        }
    }

    private static function checkMinMaxMatch(mixed $listingValue, ?int $minValue, ?int $maxValue): void
    {
        if ((!is_null($minValue) && $listingValue < $minValue) || (!is_null($maxValue) && $listingValue > $maxValue)) {
            throw new FilterMismatchException("Value" . $listingValue . " not in range $minValue-$maxValue");
        }
    }

    private static function checkEnumMatch(mixed $listingValue, ?string $filterValue, string $enumClass): void
    {
        if (!empty($filterValue) && $listingValue != $enumClass::from($filterValue)) {
            throw new FilterMismatchException("Enum match failed for value: " . $listingValue);
        }
    }
}
