<?php

namespace App\Repositories;

use App\Models\Listing;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;
use MongoDB\BSON\Regex;

class ListingRepository
{
    /**
     * @param array<mixed> $filters
     *
     * @return Paginator<Listing>
     */
    public function list(array $filters = [], int $itemsPerPage = 20): Paginator
    {
        $query = Listing::query();

        $query->when(isset($filters['collection']) && isset($filters['userId']) , function ($query) use ($filters) {
            $query->where('user.userId', $filters['userId']);
        });

        $query->when(isset($filters['q']), function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'ilike', '%' . $filters['q'] . '%')
                    ->orWhere(function ($q) use ($filters) {
                        // Explicitly define regexp and options to filter text with newline
                        $q->where('description', 'regexp', new Regex('^.*' . $filters['q'] . '.*', 'is'));
                    });
            });
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

            $query->when(is_array($filters['bedroomCount']), function ($q) use ($filters) {

                if (isset($filters['bedroomCount']['min'])) {
                    $q->where('bedroomCount', '>=', (int) $filters['bedroomCount']['min']);
                }
                if (isset($filters['bedroomCount']['max'])) {
                    $q->where('bedroomCount', '<=', (int) $filters['bedroomCount']['max']);
                }

            }, function($q) use ($filters) {
                $q->where('bedroomCount', (int) $filters['bedroomCount']);
            });

        });

        $query->when(isset($filters['bathroomCount']), function ($query) use ($filters) {

            $query->when(is_array($filters['bathroomCount']), function ($q) use ($filters) {

                if (isset($filters['bathroomCount']['min'])) {
                    $q->where('bathroomCount', '>=', (int) $filters['bathroomCount']['min']);
                }
                if (isset($filters['bathroomCount']['max'])) {
                    $q->where('bathroomCount', '<=', (int) $filters['bathroomCount']['max']);
                }

            }, function($q) use ($filters) {
                $q->where('bathroomCount', (int) $filters['bathroomCount']);
            });

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
            $query->where('ownership', 'like', $filters['ownership']);
        });

        $query->when(isset($filters['carCount']), function ($query) use ($filters) {
            $query->when(is_array($filters['carCount']), function ($q) use ($filters) {

                if (isset($filters['carCount']['min'])) {
                    $q->where('carCount', '>=', (int) $filters['carCount']['min']);
                }
                if (isset($filters['carCount']['max'])) {
                    $q->where('carCount', '<=', (int) $filters['carCount']['max']);
                }

            }, function($q) use ($filters) {
                $q->where('carCount', (int) $filters['carCount']);
            });
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
