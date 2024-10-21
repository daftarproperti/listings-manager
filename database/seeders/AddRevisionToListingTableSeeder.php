<?php

namespace Database\Seeders;

use App\Models\Listing;
use Illuminate\Database\Seeder;

class AddRevisionToListingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $result = Listing::raw(function ($collection) {
            return $collection->updateMany(
                [
                    'revision' => ['$exists' => false]
                ],
                ['$set' => ['revision' => 0]]
            );
        });

        echo "Updated revision to " . $result->getModifiedCount() . " documents.";
    }
}
