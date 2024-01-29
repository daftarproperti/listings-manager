<?php

namespace App\Repositories;

use App\Models\Property;

class PropertyRepository
{
    public function list(array $filters = [], $itemsPerPage = 20)
    {
        $query = Property::query();

        $query->when($filters['collection'] && $filters['user_id'] , function ($query) use ($filters) {
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

        $query->when(isset($filters['electricity']), function ($query) use ($filters) {
            $query->where('electricity', (int) $filters['electricity']);
        });

        return $query->paginate($itemsPerPage);
    }
}
