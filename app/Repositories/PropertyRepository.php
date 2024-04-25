<?php

namespace App\Repositories;

use App\DTO\FilterMinMax;
use App\DTO\FilterSet;
use App\Helpers\Assert;
use App\Models\Property;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;
use MongoDB\BSON\Regex;

class PropertyRepository
{
    /**
     * @param FilterSet $filterSet
     *
     * @return Paginator<Property>
     */
    public function list(FilterSet $filterSet = new FilterSet(), int $itemsPerPage = 20): Paginator
    {
        $query = Property::query();

        $query->when(isset($filterSet->q), function ($query) use ($filterSet) {
            $query->where(function ($q) use ($filterSet) {
                $q->where('title', 'ilike', '%' . $filterSet->q . '%')
                    ->orWhere(function ($q) use ($filterSet) {
                        // Explicitly define regexp and options to filter text with newline
                        $q->where('description', 'regexp', new Regex('^.*' . $filterSet->q . '.*', 'is'));
                    });
            });
        });

        $query->when(isset($filterSet->price), function ($query) use ($filterSet) {
            if ($filterSet->price instanceof FilterMinMax && isset($filterSet->price->min)) {
                $query->where('price', '>=', (int) $filterSet->price->min);
            }
            if ($filterSet->price instanceof FilterMinMax && isset($filterSet->price->max)) {
                $query->where('price', '<=', (int) $filterSet->price->max);
            }
        });

        $query->when(isset($filterSet->propertyType), function ($query) use ($filterSet) {
            $query->where('propertyType', $filterSet->propertyType);
        });

        $query->when(isset($filterSet->city), function ($query) use ($filterSet) {
            $query->where('city', $filterSet->city);
        });

        $query->when(isset($filterSet->bedroomCount), function ($query) use ($filterSet) {

            $query->when($filterSet->bedroomCount instanceof FilterMinMax, function ($q) use ($filterSet) {
                assert($filterSet->bedroomCount instanceof FilterMinMax);

                if (isset($filterSet->bedroomCount->min)) {
                    $q->where('bedroomCount', '>=', (int) $filterSet->bedroomCount->min);
                }
                if (isset($filterSet->bedroomCount->max)) {
                    $q->where('bedroomCount', '<=', (int) $filterSet->bedroomCount->max);
                }

            }, function($q) use ($filterSet) {
                $q->where('bedroomCount', Assert::int($filterSet->bedroomCount));
            });

        });

        $query->when(isset($filterSet->bathroomCount), function ($query) use ($filterSet) {

            $query->when($filterSet->bathroomCount instanceof FilterMinMax, function ($q) use ($filterSet) {
                assert($filterSet->bathroomCount instanceof FilterMinMax);

                if (isset($filterSet->bathroomCount->min)) {
                    $q->where('bathroomCount', '>=', (int) $filterSet->bathroomCount->min);
                }
                if (isset($filterSet->bathroomCount->max)) {
                    $q->where('bathroomCount', '<=', (int) $filterSet->bathroomCount->max);
                }

            }, function($q) use ($filterSet) {
                $q->where('bathroomCount',  Assert::int($filterSet->bathroomCount));
            });

        });

        $query->when(isset($filterSet->lotSize), function ($query) use ($filterSet) {
            if ($filterSet->lotSize instanceof FilterMinMax && isset($filterSet->lotSize->min)) {
                $query->where('lotSize', '>=', (int) $filterSet->lotSize->min);
            }
            if ($filterSet->lotSize instanceof FilterMinMax && isset($filterSet->lotSize->max)) {
                $query->where('lotSize', '<=', (int) $filterSet->lotSize->max);
            }
        });

        $query->when(isset($filterSet->buildingSize), function ($query) use ($filterSet) {
            if ($filterSet->buildingSize instanceof FilterMinMax && isset($filterSet->buildingSize->min)) {
                $query->where('buildingSize', '>=', (int) $filterSet->buildingSize->min);
            }
            if ($filterSet->buildingSize instanceof FilterMinMax && isset($filterSet->buildingSize->max)) {
                $query->where('buildingSize', '<=', (int) $filterSet->buildingSize->max);
            }
        });

        $query->when(isset($filterSet->ownership), function ($query) use ($filterSet) {
            $query->where('ownership', $filterSet->ownership);
        });

        $query->when(isset($filterSet->carCount), function ($query) use ($filterSet) {
            $query->when($filterSet->carCount instanceof FilterMinMax, function ($q) use ($filterSet) {
                assert($filterSet->carCount instanceof FilterMinMax);

                if (isset($filterSet->carCount->min)) {
                    $q->where('carCount', '>=', (int) $filterSet->carCount->min);
                }
                if (isset($filterSet->carCount->max)) {
                    $q->where('carCount', '<=', (int) $filterSet->carCount->max);
                }

            }, function($q) use ($filterSet) {
                $q->where('carCount',  Assert::int($filterSet->carCount));
            });
        });

        $query->when(isset($filterSet->electricPower), function ($query) use ($filterSet) {
            $query->where('electricPower',  Assert::int($filterSet->electricPower));
        });

        $query->when(isset($filterSet->sort), function ($query) use ($filterSet) {
            assert(is_string($filterSet->sort));
            $order = isset($filterSet->order) ? Str::lower(Assert::string($filterSet->order)) : 'asc';
            $sort = !in_array($filterSet->sort, ['created_at', 'updated_at']) ? Str::camel($filterSet->sort) : $filterSet->sort;

            $query->orderBy($sort, $order);
        });

        return $query->paginate($itemsPerPage);
    }
}
