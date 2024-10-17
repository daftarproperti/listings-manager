<?php

namespace App\Repositories\Admin;

use App\Models\Listing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListingRepository
{
    /**
     * @param array<mixed> $input
     * @param bool $hasAdminAttention
     * @return LengthAwarePaginator<Listing>
     */
    public function list(
        array $input = [],
        bool $hasAdminAttention = false,
        int $itemsPerPage = 10,
    ): LengthAwarePaginator {
        $query = Listing::with('adminAttentions');

        $user = null;
        if (isset($input['q'])) {
            /** @var User|null $user */
            $user = User::where('phoneNumber', $input['q'])->first();
            $query->where('address', 'like', '%' . $input['q'] . '%')
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

        if ($hasAdminAttention) {
            $query->whereHas('adminAttentions');
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
                $q->where(function ($subQuery) use ($input, $user) {
                    $subQuery->where('address', 'like', '%' . $input['q'] . '%')
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
            throw new \InvalidArgumentException('Sort parameters must be valid strings.');
        }

        $query->orderBy($sortBy, $sortOrder);

        /** @var LengthAwarePaginator<Listing> $paginator */
        $paginator = $query->paginate($itemsPerPage);
        return $paginator;
    }

    /**
     * List entries with an expired date.
     *
     * @param array<string, mixed> $input
     * @param int $itemsPerPage
     * @return LengthAwarePaginator<Listing>
     */
    public function listWithExpiredDate(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $now = Carbon::now();

        $query = Listing::where(function ($q) use ($input, $now) {
            $q->whereNotNull('expiredAt')
            ->where('expiredAt', '<', $now);

            if (isset($input['q'])) {
                /** @var User|null $user */
                $user = User::where('phoneNumber', $input['q'])
                            ->orWhere('name', 'like', '%' . $input['q'] . '%')->first();
                $q->where(function ($subQuery) use ($input, $user) {
                    $subQuery->where('address', 'like', '%' . $input['q'] . '%')
                            ->orWhere('_id', $input['q']);

                    if ($user) {
                        $subQuery->orWhere('user.userId', $user->user_id);
                    }
                });
            }
        });

        $sortBy = $input['sortBy'] ?? 'expiredAt';
        $sortOrder = $input['sortOrder'] ?? 'asc';

        if (!is_string($sortBy) || !is_string($sortOrder)) {
            throw new \InvalidArgumentException('Sort parameters must be valid strings.');
        }

        $query->orderBy($sortBy, $sortOrder);

        /** @var LengthAwarePaginator<Listing> $paginator */
        $paginator = $query->paginate($itemsPerPage);
        return $paginator;
    }
}
