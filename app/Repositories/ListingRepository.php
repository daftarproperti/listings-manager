<?php

namespace App\Repositories;

use App\Models\FilterMinMax;
use App\Models\FilterSet;
use App\Models\Listing;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use MongoDB\BSON\Regex;

class ListingRepository
{
    /**
     * @param Builder<Listing> $query
     *
     * @return Builder<Listing>
     */
    private function buildFilterQuery(Builder $query, \BackedEnum|string|int|FilterMinMax|null|bool $filter, string $column): Builder
    {
        if (is_null($filter)) return $query;

        if (is_scalar($filter) || $filter instanceof \BackedEnum) {
            // Exact filter if it's a scalar (single value like e.g. string, int, enum).
            return $query->where($column, $filter);
        }

        return $query
            ->when(isset($filter->min), function ($query) use ($filter, $column) {
                $query->where($column, '>=', $filter->min);
            })
            ->when(isset($filter->max), function ($query) use ($filter, $column) {
                $query->where($column, '<=', $filter->max);
            });
    }

    /**
     * @return Paginator<Listing>
     */
    public function list(FilterSet $filterSet = new FilterSet(), int $itemsPerPage = 20): Paginator
    {
        $query = Listing::query();

        $query->when(isset($filterSet->collection) && isset($filterSet->userId), function ($query) use ($filterSet) {
            $query->where('user.userId', $filterSet->userId);
        });

        $query->when(isset($filterSet->q), function ($query) use ($filterSet) {
            $query->where(function ($q) use ($filterSet) {
                $q->where('title', 'ilike', '%' . $filterSet->q . '%')
                    ->orWhere(function ($q) use ($filterSet) {
                        // Explicitly define regexp and options to filter text with newline
                        $q->where('description', 'regexp', new Regex('^.*' . $filterSet->q . '.*', 'is'));
                    });
            });
        });

        $this->buildFilterQuery($query, $filterSet->price, 'price');
        $this->buildFilterQuery($query, $filterSet->rentPrice, 'rentPrice');
        $this->buildFilterQuery($query, $filterSet->bedroomCount, 'bedroomCount');
        $this->buildFilterQuery($query, $filterSet->bathroomCount, 'bathroomCount');
        $this->buildFilterQuery($query, $filterSet->lotSize, 'lotSize');
        $this->buildFilterQuery($query, $filterSet->buildingSize, 'buildingSize');
        $this->buildFilterQuery($query, $filterSet->carCount, 'carCount');
        $this->buildFilterQuery($query, $filterSet->propertyType, 'propertyType');
        $this->buildFilterQuery($query, $filterSet->listingForSale, 'listingForSale');
        $this->buildFilterQuery($query, $filterSet->listingForRent, 'listingForRent');
        $this->buildFilterQuery($query, $filterSet->ownership, 'ownership');
        $this->buildFilterQuery($query, $filterSet->electricPower, 'electricPower');
        $this->buildFilterQuery($query, $filterSet->city, 'city');
        $this->buildFilterQuery($query, $filterSet->cityId, 'cityId');

        $query->when(isset($filterSet->sort), function ($query) use ($filterSet) {
            assert(is_string($filterSet->sort));
            $order = isset($filterSet->order) ? Str::lower($filterSet->order) : 'asc';
            $sort = !in_array($filterSet->sort, ['created_at', 'updated_at']) ? Str::camel($filterSet->sort) : $filterSet->sort;

            $query->orderBy($sort, $order);
        });

        return $query->paginate($itemsPerPage);
    }
}
