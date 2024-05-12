<?php

namespace Database\Seeders;

use App\Models\Enums\VerifyStatus;
use App\Models\Listing;
use Illuminate\Database\Seeder;

class AddVerifyStatusToListingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $result = Listing::raw(function ($collection) {
            return $collection->updateMany([], ['$set' => ['verifyStatus' => VerifyStatus::ON_REVIEW]]);
        });
        echo "Updated " . $result->getModifiedCount() . " documents.";
    }
}
