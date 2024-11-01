<?php

namespace App\Observers;

use App\Helpers\Queue;
use App\Jobs\SyncListingToGCS;
use App\Jobs\Web3Listing;
use App\Models\AdminNote;
use App\Models\ListingHistory;
use App\Models\Enums\VerifyStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Listing;
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
            // Sync to web3 of this Listing created event.
            $listingId = $listing->listingId;

            SyncListingToGCS::dispatch($listingId)->onQueue(Queue::getQueueName('generic'));
        } catch (\Throwable $th) {
            Log::error('Error copy to property: ' . $th->getMessage());
        }

        try {
            /** @var User $user */
            $user = Auth::user();
            $impersonator = $user->getImpersonatedBy();

            ListingHistory::create([
                'listingId' => $listing->id,
                'actor' => $user->phoneNumber,
                'impersonator' => $impersonator,
                'before' => json_encode([]),
                'after' => json_encode($listing->attributesToArray()),
                'changes' => json_encode([
                    'status' => [
                        'before' => null,
                        'after' => 'created',
                    ],
                ]),
            ]);
        } catch (\Throwable $th) {
            Log::error('Error writing histories: ' . $th->getMessage());
        }
    }

    /**
     * Handle the Listing "updated" event.
     */
    public function updated(Listing $listing): void
    {
        try {
            SyncListingToGCS::dispatch($listing->listingId)->onQueue(Queue::getQueueName('generic'));
        } catch (\Throwable $th) {
            Log::error('Error sync to property: ' . $th->getMessage());
        }

        try {
            $originalAttributes = $listing->getOriginal();
            $updatedAttributes = $listing->getAttributes();

            // Include all changes to changelog except for excluded fields
            $excludedFields = ['updated_at', 'user', 'source'];
            $rawChanges = array_diff_key($listing->getChanges(), array_flip($excludedFields));

            $changes = [];
            foreach ($rawChanges as $field => $newValue) {
                $originalValue = $listing->getOriginal($field);

                $changes[$field] = [
                    'before' => $originalValue,
                    'after' => $newValue,
                ];
            }

            /** @var User $user */
            $user = Auth::user();
            $impersonator = $user->getImpersonatedBy();

            ListingHistory::create([
                'listingId' => $listing->id,
                'actor' => $user->phoneNumber,
                'impersonator' => $impersonator,
                'before' => json_encode($originalAttributes),
                'after' => json_encode($updatedAttributes),
                'changes' => json_encode($changes),
            ]);
        } catch (\Throwable $th) {
            Log::error('Error writing histories: ' . $th->getMessage());
        }

        try {
            $originalVerifyStatus = $listing->getOriginal('verifyStatus');

            if ($originalVerifyStatus === VerifyStatus::APPROVED) {
                // If the status is not approved anymore
                // Publish the delete listing to web3
                if ($listing->verifyStatus !== VerifyStatus::APPROVED) {
                    $operationType = 'DELETE';
                } else {
                    $operationType = 'UPDATE';
                }
            }

            if ($originalVerifyStatus !== VerifyStatus::APPROVED) {
                // If verifyStatus not changed to approved
                // Do nothing
                if ($listing->verifyStatus !== VerifyStatus::APPROVED) {
                    return;
                }

                $operationType = 'ADD';
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
        $listing->revision = 0;

        $attributes = $listing->getAttributes();
        $minimumFill = 50;

        $filled = count(array_filter($attributes)) / count($attributes) * 100;

        if ($filled < $minimumFill) {
            Log::warning('Not creating Listing due to empty detected: ' . print_r($listing->attributesToArray(), true));
            return false;
        }

        $adminNote = [
            'message' => "Listing baru akan melalui proses tinjauan oleh admin.\n" .
                "Jika ada informasi yang harus diubah, maka akan ditambahkan di catatan ini.\n" .
                "Silahkan pantau catatan ini.\n",
            'email' => 'system@daftarproperti.org',
            'date' => Carbon::now()->floorSecond(),
        ];
        $listing->adminNote = AdminNote::from($adminNote);

        return true;
    }

    /**
     * Handle the Listing "deleting" event.
     * @param Listing $listing
     * @return void
     */
    public function deleting(Listing $listing): void
    {
        try {
            ListingHistory::create([
                'listingId' => $listing->id,
                'before' => json_encode($listing->attributesToArray()),
                'after' => json_encode([]),
                'changes' => json_encode([
                    'status' => [
                        'before' => null,
                        'after' => 'deleted',
                    ],
                ]),
            ]);
        } catch (\Throwable $th) {
            Log::error('Error writing histories: ' . $th->getMessage());
        }
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
    }
}
