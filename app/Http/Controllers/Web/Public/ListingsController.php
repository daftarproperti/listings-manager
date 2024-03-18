<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\TelegramUser;
use Illuminate\Contracts\View\View;

class ListingsController extends Controller
{
    public function detail(Listing $listing): View
    {
        $user = TelegramUser::where('user_id', $listing->user->userId ?? '')->first();

        return view('Public/Listing', [
            'agent' => $user,
            'listing' => $listing,
        ]);
    }
}
