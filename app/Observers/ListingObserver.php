<?php

namespace App\Observers;

use App\Helpers\Queue;
use App\Jobs\SyncListingToGCS;
use App\Jobs\Web3Listing;
use App\Models\Enums\VerifyStatus;
use Illuminate\Support\Facades\Auth;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
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

            // Sync to web3 of this Listing created event.
            $listingId = $listing->listingId;

            SyncListingToGCS::dispatch($listingId)->onQueue(Queue::getQueueName('generic'));
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

            SyncListingToGCS::dispatch($listing->listingId)->onQueue(Queue::getQueueName('generic'));
        } catch (\Throwable $th) {
            Log::error('Error sync to property: ' . $th->getMessage());
        }

        try {
            $originalVerifyStatus = $listing->getOriginal('verifyStatus');

            if ($originalVerifyStatus === VerifyStatus::APPROVED) {
                // If the status is not approved anymore
                // Publish the delete listing to web3
                if ($listing->verifyStatus !== VerifyStatus::APPROVED) {
                    $operationType = "DELETE";
                } else {
                    $operationType = "UPDATE";
                }
            }

            if ($originalVerifyStatus !== VerifyStatus::APPROVED) {
                // If verifyStatus not changed to approved
                // Do nothing
                if ($listing->verifyStatus !== VerifyStatus::APPROVED) {
                    return;
                }

                $operationType = "ADD";
            }

            Web3Listing::dispatch(
                $listing,
                $operationType,
            )->onQueue(Queue::getQueueName('generic'));
        } catch (\Throwable $th) {
            Log::error('Error sync to blockchain: ' . $th->getMessage());
        }
    }

    /**
     * Handle the Listing "creating" event.
     * @param Listing $listing
     * @return bool
     */

    public function creating(Listing $listing): bool
    {
        /** @var User $user */
        $user = Auth::user();

        $maxListings = config('services.max_listings_per_user', null);
        if ($maxListings !== null && is_numeric($maxListings)) {
            $maxListings = (int) $maxListings;
            $count = Listing::where('user.userId', $user->user_id)->count();

            if ($count >= $maxListings) {
                throw new \Exception("Untuk sementara batas maksimum listing setiap user adalah $maxListings.");
            }
        }

        $listing->verifyStatus = VerifyStatus::ON_REVIEW;
        $listing->listingId = random_int(1, PHP_INT_MAX);

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
        try {
            $originalVerifyStatus = $listing->getOriginal('verifyStatus');

            // If an approved listing is deleted
            // Delete it from web3
            if ($originalVerifyStatus === VerifyStatus::APPROVED) {
                Web3Listing::dispatch(
                    $listing,
                    `DELETE`,
                )->onQueue(Queue::getQueueName('generic'));
            }
        } catch (\Throwable $th) {
            Log::error('Error sync to blockchain: ' . $th->getMessage());
        }

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
