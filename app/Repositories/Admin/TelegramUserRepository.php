<?php

namespace App\Repositories\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TelegramUserRepository
{
    /**
     * @param array<mixed> $input
     *
     * @return LengthAwarePaginator<User>
     */
    public function list(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = User::query();
        $query->when(isset($input['q']), function ($query) use ($input) {
            $query->where(function ($q) use ($input) {
                $q->where('user_id', 'like', '%' . $input['q'] . '%')
                    ->orWhere('username', 'like', '%' . $input['q'] . '%')
                    ->orWhere('name', 'like', '%' . $input['q'] . '%')
                    ->orWhere('phoneNumber', 'like', '%' . $input['q'] . '%');
            });
        });

        return $query->paginate($itemsPerPage);
    }
}
