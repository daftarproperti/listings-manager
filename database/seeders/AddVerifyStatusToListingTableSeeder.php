<?php

namespace Database\Seeders;

use App\Models\Enums\VerifyStatus;
use App\Models\Listing;
use Illuminate\Database\Seeder;

// TODO: This seeder should be removed once we backfill all review status.
class AddVerifyStatusToListingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $result = Listing::raw(function ($collection) {
            return $collection->updateMany(
                ['verifyStatus' => ['$exists' => false]],  // Only update documents where 'verifyStatus' does not exist
                ['$set' => ['verifyStatus' => VerifyStatus::ON_REVIEW]]
            );
        });

        echo "Updated " . $result->getModifiedCount() . " documents.";
    }
}
