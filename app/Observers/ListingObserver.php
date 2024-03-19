<?php

namespace App\Observers;

use App\Helpers\Assert;
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
            $dataToCopy = collect($listing->toArray())->except(['_id', 'user', 'created_at', 'updated_at']);
            $convertObjectKey = ['contact', 'coordinate'];

            $property = new Property();

            $dataToCopy = $dataToCopy->map(function ($value, $key) use ($convertObjectKey, &$property) {
                if (in_array($key, $convertObjectKey)) {
                    $property->{$key} = (object) $value;
                    return;
                }
                $property->{$key} = $value;
                return;
            });

            /**
             * in next iteration 1 property will have more than 1 listing, so we use array for listings attribute
             */
            $property->listings = [Assert::string($listing->id)];

            $property->save();
        } catch (\Throwable $th) {
            Log::error('Error copy to property: ' . $th->getMessage());
        }
    }
}
