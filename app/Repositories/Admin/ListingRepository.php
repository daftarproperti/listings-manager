<?php

namespace App\Repositories\Admin;

use App\Models\Listing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListingRepository
{
    /**
     * @param array<mixed> $input
     *
     * @return LengthAwarePaginator<Listing>
     */
    public function list(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = Listing::query();

        $query->when(isset($input['q']), function ($query) use ($input) {
            $query->where('title', 'like', '%' . $input['q'] . '%')
                ->orWhere('_id', $input['q']);
        });

        $query->when(isset($input['verifyStatus']), function ($query) use ($input) {
            $query->where('verifyStatus', $input['verifyStatus']);
        });

        return $query->paginate($itemsPerPage);
    }
}
