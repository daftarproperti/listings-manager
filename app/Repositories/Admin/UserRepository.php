<?php

namespace App\Repositories\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
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
                $q->where('name', 'like', '%' . $input['q'] . '%')
                    ->orWhere('phoneNumber', 'like', '%' . $input['q'] . '%');
            });
        });

        $query->when(isset($input['delegatePhone']), function ($query) use ($input) {
            $query->where('delegatePhone', $input['delegatePhone']);
        });

        $query->when(isset($input['isDelegateEligible']), function ($query) use ($input) {
            $input['isDelegateEligible'] = filter_var($input['isDelegateEligible'], FILTER_VALIDATE_BOOLEAN);
            $query->where('isDelegateEligible', $input['isDelegateEligible']);
        });

        return $query->paginate($itemsPerPage);
    }

    public function getById(string $id): ?User
    {
        return User::query()->findOrFail($id);
    }
}
