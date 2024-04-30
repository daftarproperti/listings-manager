<?php

namespace App\Models;

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

    protected $casts = [
        'filterSet' => FilterSet::class,
    ];

    protected $fillable = [
        'userId',
        'title',
        'filterSet',
    ];

    public static function countSavedSearchMatches(mixed $listingId): int
    {
        /** @var null|Listing $listing */
        $listing = Listing::where('_id', $listingId)->first();
        if (!$listing) {
            return 0;
        }

        $filters = SavedSearch::all();

        $matchingCount = 0;
        foreach ($filters as $filter) {
            try {
                // Check for exact matches on simple fields
                self::checkExactMatch($listing->city, $filter->filterSet->city);
                self::checkExactMatch($listing->floorCount, $filter->filterSet->floorCount);
                self::checkExactMatch($listing->electricPower, $filter->filterSet->electricPower);

                // Check for range matches on various fields
                self::checkMinMaxMatch($listing->price, $filter->filterSet->price);
                self::checkMinMaxMatch($listing->bedroomCount, $filter->filterSet->bedroomCount);
                self::checkMinMaxMatch($listing->bathroomCount, $filter->filterSet->bathroomCount);
                self::checkMinMaxMatch($listing->lotSize, $filter->filterSet->lotSize);
                self::checkMinMaxMatch($listing->buildingSize, $filter->filterSet->buildingSize);
                self::checkMinMaxMatch($listing->carCount, $filter->filterSet->carCount);

                // Check for enum matches
                self::checkEnumMatch($listing->propertyType, $filter->filterSet->propertyType);
                self::checkEnumMatch($listing->propertyType, $filter->filterSet->propertyType);
                self::checkEnumMatch($listing->ownership, $filter->filterSet->ownership);
                self::checkEnumMatch($listing->facing, $filter->filterSet->facing);
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

    private static function checkExactMatch(int|string|null $listingValue, int|string|null $filterValue): void
    {
        if (!is_null($listingValue) && !is_null($filterValue) && $listingValue !== $filterValue) {
            throw new FilterMismatchException("Exact match failed for value: " . $listingValue);
        }
    }

    private static function checkMinMaxMatch(int|float|null $listingValue, int|FilterMinMax|null $filterValue): void
    {
        if (!is_null($listingValue) && $filterValue instanceof FilterMinMax) {
            if ((!is_null($filterValue->min) && $listingValue < $filterValue->min) || (!is_null($filterValue->max) && $listingValue > $filterValue->max)) {
                throw new FilterMismatchException("Value" . $listingValue . " not in range " . $filterValue->min . "-" . $filterValue->max);
            }
        }
        if (!is_null($listingValue) && is_numeric($filterValue) && $listingValue !== $filterValue) {
            throw new FilterMismatchException("Exact match failed for value: " . $listingValue);
        }
    }

    private static function checkEnumMatch(\BackedEnum|null $listingValue, \BackedEnum|null $filterValue): void
    {
        if (!is_null($listingValue) && !is_null($filterValue) && $listingValue != $filterValue) {
            throw new FilterMismatchException("Enum match failed for value: " . $listingValue->value);
        }
    }
}
