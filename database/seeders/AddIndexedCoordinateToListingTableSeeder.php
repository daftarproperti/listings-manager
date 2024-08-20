<?php

namespace Database\Seeders;

use App\Models\Listing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddIndexedCoordinateToListingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listings = Listing::where('coordinate', '!=', null)
                           ->where('indexedCoordinate', '=', null)
                           ->get();

        $updatedCount = 0;
        foreach ($listings as $listing) {
            $coordinate = $listing->coordinate;
                
            if (isset($coordinate->latitude) && isset($coordinate->longitude)) {
                $longitude = $coordinate->longitude;
                $latitude = $coordinate->latitude;
                
                $geoJson = [
                    'type' => 'Point',
                    'coordinates' => [$longitude, $latitude]
                ];
                
                $listing->indexedCoordinate = $geoJson;
                $listing->save();
                $updatedCount++;
            }
        }

        echo "Updated " . $updatedCount . " documents to indexed coordinate.";
    }
}
