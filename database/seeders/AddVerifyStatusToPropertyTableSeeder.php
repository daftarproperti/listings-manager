<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\VerifyStatus;
use Illuminate\Database\Seeder;

class AddVerifyStatusToPropertyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $result = Property::raw(function ($collection) {
            return $collection->updateMany([], ['$set' => ['verifyStatus' => VerifyStatus::ON_REVIEW]]);
        });
        echo "Updated " . $result->getModifiedCount() . " documents.";
    }
}
