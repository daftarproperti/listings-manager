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
            $query->where(function ($q) use ($input) {
                $q->where('title', 'like', '%' . $input['q'] . '%')
                    ->orWhere('user.name', 'like', '%' . $input['q'] . '%')
                    ->orWhere('user.userId', 'like', '%' . $input['q'] . '%')
                    ->orWhere('user.userName', 'like', '%' . $input['q'] . '%');
            });
        });

        return $query->paginate($itemsPerPage);
    }
}
