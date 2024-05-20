<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinceData = storage_path('provinces.json');
        $provinces = json_decode(File::get($provinceData));

        $cityData = storage_path('cities.json');
        $cities = json_decode(File::get($cityData));

        foreach ($cities as $cityItem)
        {
            $city = City::where('administrativeId', $cityItem->administrativeId)->first() ?? new City();

            $province = collect($provinces)->filter(
                function ($p) use ($cityItem) {
                    return $p->administrativeId == $cityItem->administrativeProvinceId;
                })?->first();

            $city->administrativeId = $cityItem->administrativeId;
            $city->osmId = $cityItem->osmId;
            $city->osmType = $cityItem->osmType;
            $city->name = $cityItem->name;
            $city->provinceName = $province->name;
            $city->location = $cityItem->location;

            $city->save();
        }
    }
}
