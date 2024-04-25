<?php

namespace App\Repositories;

use App\Models\SavedSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SavedSearchRepository
{
    /**
     * @param array<mixed> $input
     *
     * @return LengthAwarePaginator<SavedSearch>
     */
    public function list(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = SavedSearch::query();
        $query->when(isset($input['userId']), function ($query) use ($input) {
            $query->where('userId', $input['userId']);
        });

        return $query->paginate($itemsPerPage);
    }
}
