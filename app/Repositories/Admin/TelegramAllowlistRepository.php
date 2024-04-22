<?php

namespace App\Repositories\Admin;

use App\Models\TelegramAllowlistGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TelegramAllowlistRepository
{
    /**
     * @return LengthAwarePaginator<TelegramAllowlistGroup>
     */
    public function list(int $itemsPerPage = 10): LengthAwarePaginator
    {
        $query = TelegramAllowlistGroup::query();

        return $query->paginate($itemsPerPage);
    }
}
