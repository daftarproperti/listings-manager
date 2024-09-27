<?php

namespace App\Repositories\Admin;

use App\Models\Closing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClosingRepository
{
    /**
     * @param array<mixed> $input
     * @return LengthAwarePaginator<Closing>
     */
    public function list(array $input = [], int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = Closing::query();

        if (isset($input['q'])) {
            $query->where('clientPhoneNumber', 'like', '%' . $input['q'] . '%')
                ->orWhere('clientName', 'like', '%' . $input['q'] . '%');
        }

        if (isset($input['sortBy']) && isset($input['sortOrder'])) {
            $query->orderBy($input['sortBy'], $input['sortOrder']);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($itemsPerPage);
    }
}
