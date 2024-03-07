<?php

namespace App\Repositories;

use App\Helpers\Assert;
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

        $query->when(isset($filters['q']), function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['q'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['q'] . '%');
            });
        });

        $query->when(isset($filters['price']), function ($query) use ($filters) {
            if (is_array($filters['price']) && isset($filters['price']['min'])) {
                $query->where('price', '>=', (int) $filters['price']['min']);
            }
            if (is_array($filters['price']) && isset($filters['price']['max'])) {
                $query->where('price', '<=', (int) $filters['price']['max']);
            }
        });

        $query->when(isset($filters['type']), function ($query) use ($filters) {
            $query->where('type', $filters['type']);
        });

        $query->when(isset($filters['bedroomCount']), function ($query) use ($filters) {

            $query->when(is_array($filters['bedroomCount']), function ($q) use ($filters) {

                if (is_array($filters['bedroomCount']) && isset($filters['bedroomCount']['min'])) {
                    $q->where('bedroomCount', '>=', (int) $filters['bedroomCount']['min']);
                }
                if (is_array($filters['bedroomCount']) && isset($filters['bedroomCount']['max'])) {
                    $q->where('bedroomCount', '<=', (int) $filters['bedroomCount']['max']);
                }

            }, function($q) use ($filters) {
                $q->where('bedroomCount', Assert::int($filters['bedroomCount']));
            });

        });

        $query->when(isset($filters['bathroomCount']), function ($query) use ($filters) {

            $query->when(is_array($filters['bathroomCount']), function ($q) use ($filters) {

                if (is_array($filters['bathroomCount']) && isset($filters['bathroomCount']['min'])) {
                    $q->where('bathroomCount', '>=', (int) $filters['bathroomCount']['min']);
                }
                if (is_array($filters['bathroomCount']) && isset($filters['bathroomCount']['max'])) {
                    $q->where('bathroomCount', '<=', (int) $filters['bathroomCount']['max']);
                }

            }, function($q) use ($filters) {
                $q->where('bathroomCount',  Assert::int($filters['bathroomCount']));
            });

        });

        $query->when(isset($filters['lotSize']), function ($query) use ($filters) {
            if (is_array($filters['lotSize']) && isset($filters['lotSize']['min'])) {
                $query->where('lotSize', '>=', (int) $filters['lotSize']['min']);
            }
            if (is_array($filters['lotSize']) && isset($filters['lotSize']['max'])) {
                $query->where('lotSize', '<=', (int) $filters['lotSize']['max']);
            }
        });

        $query->when(isset($filters['buildingSize']), function ($query) use ($filters) {
            if (is_array($filters['buildingSize']) && isset($filters['buildingSize']['min'])) {
                $query->where('buildingSize', '>=', (int) $filters['buildingSize']['min']);
            }
            if (is_array($filters['buildingSize']) && isset($filters['buildingSize']['max'])) {
                $query->where('buildingSize', '<=', (int) $filters['buildingSize']['max']);
            }
        });

        $query->when(isset($filters['ownership']), function ($query) use ($filters) {
            $query->where('ownership', 'like', $filters['ownership']);
        });

        $query->when(isset($filters['carCount']), function ($query) use ($filters) {
            $query->when(is_array($filters['carCount']), function ($q) use ($filters) {

                if (is_array($filters['carCount']) && isset($filters['carCount']['min'])) {
                    $q->where('carCount', '>=', (int) $filters['carCount']['min']);
                }
                if (is_array($filters['carCount']) && isset($filters['carCount']['max'])) {
                    $q->where('carCount', '<=', (int) $filters['carCount']['max']);
                }

            }, function($q) use ($filters) {
                $q->where('carCount',  Assert::int($filters['carCount']));
            });
        });

        $query->when(isset($filters['electricPower']), function ($query) use ($filters) {
            $query->where('electricPower',  Assert::int($filters['electricPower']));
        });

        $query->when(isset($filters['sort']), function ($query) use ($filters) {
            $order = isset($filters['order']) &&
                in_array(strtolower(Assert::string($filters['order'])), ['asc', 'desc']) ? strtolower(Assert::string($filters['order'])) : 'asc';
            $sort = Str::camel($filters['sort']);

            $query->orderBy($sort, $order);
        });

        return $query->paginate($itemsPerPage);
    }
}
