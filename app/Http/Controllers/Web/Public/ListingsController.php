<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Contracts\View\View;

class ListingsController extends Controller
{
    public function detail(Listing $listing): View
    {
        $user = User::where('user_id', $listing->user->userId ?? '')->first();

        return view('public/listing', [
            'agent' => $user,
            'listing' => $listing,
        ]);
    }
}
