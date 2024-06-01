<?php

namespace App\Observers;

use App\Models\Listing;
use App\Models\Property;
use Illuminate\Support\Facades\Log;


class ListingObserver
{
    /**
     * Handle the Listing "created" event.
     */
    public function created(Listing $listing): void
    {
        try {

            $property = new Property();
            $this->fillPropertyFromListing($listing, $property);

            /**
             * in next iteration 1 property will have more than 1 listing, so we use array for listings attribute
             */
            $property->listings = [$listing->id];
            $property->save();

        } catch (\Throwable $th) {
            Log::error('Error copy to property: ' . $th->getMessage());
        }
    }

    /**
     * Handle the Listing "updated" event.
     */
    public function updated(Listing $listing): void
    {
        try {

            $property = Property::where('listings', $listing->id)->first();
            $this->fillPropertyFromListing($listing, $property);
            $property->save();

        } catch (\Throwable $th) {
            Log::error('Error sync to property: ' . $th->getMessage());
        }
    }

    /**
     * Handle the Listing "creating" event.
     * @param Listing $listing
     * @return bool
     */

    public function creating(Listing $listing): bool
    {
        $attributes = $listing->getAttributes();
        $minimumFill = 50;

        $filled = count(array_filter($attributes)) / count($attributes) * 100;

        if ($filled < $minimumFill) {
            Log::warning("Not creating Listing due to empty detected: " . print_r($listing->attributesToArray(), true));
            return false;
        }

        return true;
    }

    /**
     * Handle the Listing "deleted" event.
     * @param Listing $listing
     * @return void
     */
    public function deleted(Listing $listing): void
    {
        $property = Property::where('listings', $listing->id)->first();
        if ($property) {
            $property->delete();
        }
    }

    /**
     * @param Listing $listing
     * @param Property $property
     * @return void
     */
    private function fillPropertyFromListing(Listing $listing, Property &$property): void
    {
        $dataToSync = collect($listing->toArray())->except(['_id', 'user', 'created_at', 'updated_at']);

        $convertObjectKey = ['contact', 'coordinate'];

        $dataToSync->map(function ($value, $key) use ($convertObjectKey, &$property) {
            if (in_array($key, $convertObjectKey)) {
                $property->{$key} = (object) $value;
                return;
            }
            $property->{$key} = $value;
            return;
        });
    }
}
