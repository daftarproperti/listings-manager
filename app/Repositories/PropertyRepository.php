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

        return $query->paginate($itemsPerPage);
    }
}
