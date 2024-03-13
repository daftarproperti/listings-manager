<?php

namespace App\Repositories\Admin;

use App\Models\TelegramUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TelegramUserRepository
{
    /**
     * @param array<mixed> $input
     *
     * @return LengthAwarePaginator<TelegramUser>
     */
    public function list(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = TelegramUser::query();
        $query->when(isset($input['q']), function ($query) use ($input) {
            $query->where(function ($q) use ($input) {
                $q->where('user_id', 'like', '%' . $input['q'] . '%')
                    ->orWhere('username', 'like', '%' . $input['q'] . '%')
                    ->orWhere('profile.name', 'like', '%' . $input['q'] . '%')
                    ->orWhere('profile.phoneNumber', 'like', '%' . $input['q'] . '%');
            });
        });

        return $query->paginate($itemsPerPage);
    }
}
