<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Enums\ActiveStatus;
use App\Models\Enums\VerifyStatus;
use Illuminate\Database\Seeder;

class AddActiveStatusToListingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $result = Listing::raw(function ($collection) {
            return $collection->updateMany(
                [
                    'activeStatus' => ['$exists' => false],
                    'verifyStatus' => VerifyStatus::APPROVED 
                ],
                ['$set' => ['activeStatus' => ActiveStatus::ACTIVE]]
            );
        });

        echo "Updated " . $result->getModifiedCount() . " documents to active status.";
    }
}
