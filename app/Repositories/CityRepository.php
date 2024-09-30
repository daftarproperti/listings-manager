<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\Coordinate;
use Illuminate\Database\Eloquent\Collection;

class CityRepository
{
    /**
     * @param string|null $keyword
     * @param Coordinate|null $userLocation
     * @param int $limit
     * @return Collection<int, City>
     */
    public function searchByKeyword(?string $keyword, ?Coordinate $userLocation = null, int $limit = 20): Collection
    {
        if ($userLocation) {
            return $this->searchWithUserLocation($keyword, $userLocation, $limit);
        } else {
            return $this->searchWithoutUserLocation($keyword, $limit);
        }
    }

    /**
     * @param int $cityId
     * @return City|null
     */
    public function getCityById(int $cityId): ?City
    {
        /** @var City $city|null */
        $city = City::where('osmId', $cityId)->first() ?? null;
        return $city;
    }

    /**
     * @param string|null $keyword
     * @param Coordinate $userLocation
     * @param int $limit
     * @return Collection<int, City>
     */
    private function searchWithUserLocation(?string $keyword, Coordinate $userLocation, int $limit): Collection
    {
        $longitude = $userLocation->longitude;
        $latitude = $userLocation->latitude;

        $cityCollections = City::raw(function ($collection) use ($keyword, $longitude, $latitude, $limit) {
            $point = [
                'type' => 'Point',
                'coordinates' => [$longitude, $latitude],
            ];

            return $collection->aggregate([
                ['$geoNear' => [
                    'near' => $point,
                    'spherical' => true,
                    'distanceField' => 'distance',
                    'distanceMultiplier' => 6371, // Earth's radius in kilometers
                    'query' => ['name' => ['$regex' => $keyword ?? '', '$options' => 'i']],
                ]],
                ['$limit' => $limit],
                ['$sort' => ['distance' => 1]],
            ]);
        });

        /** @var Collection<int, City> $collections */
        $collections = new Collection($cityCollections);
        return $collections;
    }

    /**
     * @param string|null $keyword
     * @param int $limit
     * @return Collection<int, City>
     */
    private function searchWithoutUserLocation(?string $keyword, int $limit): Collection
    {
        /** @var Collection<int, City> $collections */
        $collections = City::raw(function ($collection) use ($keyword, $limit) {
            return $collection->aggregate([
                ['$match' => ['name' => ['$regex' => $keyword ?? '', '$options' => 'i']]],
                ['$sort' => ['name' => 1]],
                ['$limit' => $limit],
            ]);
        });

        return $collections;
    }
}
