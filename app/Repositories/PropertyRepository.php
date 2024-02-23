<?php

namespace App\Repositories;

use App\Models\Property;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;

class PropertyRepository
{
    /**
     * @param array<mixed> $filters
     *
     * @return Paginator<Property>
     */
    public function list(array $filters = [], int $itemsPerPage = 20): Paginator
    {
        $query = Property::query();

        $query->when(isset($filters['collection']) && isset($filters['userId']) , function ($query) use ($filters) {
            $query->where('user.userId', $filters['userId']);
        });

        $query->when(isset($filters['price']), function ($query) use ($filters) {
            if (isset($filters['price']['min'])) {
                $query->where('price', '>=', (int) $filters['price']['min']);
            }
            if (isset($filters['price']['max'])) {
                $query->where('price', '<=', (int) $filters['price']['max']);
            }
        });

        $query->when(isset($filters['type']), function ($query) use ($filters) {
            $query->where('type', $filters['type']);
        });

        $query->when(isset($filters['bedroomCount']), function ($query) use ($filters) {
            $query->where('bedroomCount', (int) $filters['bedroomCount']);
        });

        $query->when(isset($filters['bathroomCount']), function ($query) use ($filters) {
            $query->where('bathroomCount', (int) $filters['bathroomCount']);
        });

        $query->when(isset($filters['lotSize']), function ($query) use ($filters) {
            if (isset($filters['lotSize']['min'])) {
                $query->where('lotSize', '>=', (int) $filters['lotSize']['min']);
            }
            if (isset($filters['lotSize']['max'])) {
                $query->where('lotSize', '<=', (int) $filters['lotSize']['max']);
            }
        });

        $query->when(isset($filters['buildingSize']), function ($query) use ($filters) {
            if (isset($filters['buildingSize']['min'])) {
                $query->where('buildingSize', '>=', (int) $filters['buildingSize']['min']);
            }
            if (isset($filters['buildingSize']['max'])) {
                $query->where('buildingSize', '<=', (int) $filters['buildingSize']['max']);
            }
        });

        $query->when(isset($filters['ownership']), function ($query) use ($filters) {
            $query->where('ownership', $filters['ownership']);
        });

        $query->when(isset($filters['carCount']), function ($query) use ($filters) {
            $query->where('carCount', (int) $filters['carCount']);
        });

        $query->when(isset($filters['electricPower']), function ($query) use ($filters) {
            $query->where('electricPower', (int) $filters['electricPower']);
        });

        $query->when(isset($filters['sort']), function ($query) use ($filters) {
            $order = isset($filters['order']) &&
                in_array(strtolower($filters['order']), ['asc', 'desc']) ? strtolower($filters['order']) : 'asc';
            $sort = Str::camel($filters['sort']);

            $query->orderBy($sort, $order);
        });

        return $query->paginate($itemsPerPage);
    }
}
