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

        $query->when(isset($filters['collection']) && isset($filters['user_id']) , function ($query) use ($filters) {
            $query->where('user.userId', $filters['user_id']);
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

        $query->when(isset($filters['bedroom_count']), function ($query) use ($filters) {
            $query->where('bedroomCount', (int) $filters['bedroom_count']);
        });

        $query->when(isset($filters['bathroom_count']), function ($query) use ($filters) {
            $query->where('bathroomCount', (int) $filters['bathroom_count']);
        });

        $query->when(isset($filters['lot_size']), function ($query) use ($filters) {
            if (isset($filters['lot_size']['min'])) {
                $query->where('lotSize', '>=', (int) $filters['lot_size']['min']);
            }
            if (isset($filters['lot_size']['max'])) {
                $query->where('lotSize', '<=', (int) $filters['lot_size']['max']);
            }
        });

        $query->when(isset($filters['building_size']), function ($query) use ($filters) {
            if (isset($filters['building_size']['min'])) {
                $query->where('buildingSize', '>=', (int) $filters['building_size']['min']);
            }
            if (isset($filters['building_size']['max'])) {
                $query->where('buildingSize', '<=', (int) $filters['building_size']['max']);
            }
        });

        $query->when(isset($filters['ownership']), function ($query) use ($filters) {
            $query->where('ownership', $filters['ownership']);
        });

        $query->when(isset($filters['car_count']), function ($query) use ($filters) {
            $query->where('carCount', (int) $filters['car_count']);
        });

        $query->when(isset($filters['electric_power']), function ($query) use ($filters) {
            $query->where('electricPower', (int) $filters['electric_power']);
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
