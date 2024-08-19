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

        if (isset($input['sortBy']) && isset($input['sortOrder'])) {
            $query->orderBy($input['sortBy'], $input['sortOrder']);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($itemsPerPage);
    }

    /**
     * List entries with a cancellation note.
     *
     * @param array<string, mixed> $input
     * @param int $itemsPerPage
     * @return LengthAwarePaginator<Listing>
     */
    public function listWithCancellationNote(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = Listing::where(function ($q) use ($input) {
            $q->whereNotNull('cancellationNote')
            ->where('cancellationNote', '!=', '');

            if (isset($input['q'])) {
                /** @var User|null $user */
                $user = User::where('phoneNumber', $input['q'])
                            ->orWhere('name', 'like', '%' . $input['q'] . '%')->first();
                $q->where(function($subQuery) use ($input, $user) {
                    $subQuery->where('title', 'like', '%' . $input['q'] . '%')
                            ->orWhere('_id', $input['q']);

                    if ($user) {
                        $subQuery->orWhere('user.userId', $user->user_id);
                    }
                });
            }
        });

        $sortBy = $input['sortBy'] ?? 'updated_at'; 
        $sortOrder = $input['sortOrder'] ?? 'desc'; 

        if (!is_string($sortBy) || !is_string($sortOrder)) {
            throw new \InvalidArgumentException("Sort parameters must be valid strings.");
        }

        $query->orderBy($sortBy, $sortOrder);

        /** @var LengthAwarePaginator<Listing> $paginator */
        $paginator = $query->paginate($itemsPerPage);
        return $paginator;
    }

}
