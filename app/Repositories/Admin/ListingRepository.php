<?php

namespace App\Repositories\Admin;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListingRepository
{
    /**
     * @param array<mixed> $input
     * @return LengthAwarePaginator<Listing>
     */
    public function list(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = Listing::query();

        $user = null;
        if (isset($input['q'])) {
            /** @var User|null $user */
            $user = User::where('phoneNumber', $input['q'])->first();
            $query->where('title', 'like', '%' . $input['q'] . '%')
                  ->orWhere('_id', $input['q']);

            if ($user) {                
                $query->orWhere('user.userId', $user->user_id);
            }
        }

        $query->when(isset($input['verifyStatus']), function ($query) use ($input) {
            $query->where('verifyStatus', $input['verifyStatus']);
        });

        return $query->paginate($itemsPerPage);
    }
}
