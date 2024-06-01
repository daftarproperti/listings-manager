<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ListingsController extends Controller
{
    public function detail(Listing $listing, Request $request): View|JsonResponse
    {
        $user = User::where('user_id', $listing->user->userId ?? '')->first();

        if ($request->wantsJson() || $request->query('format') === 'json') {
            return response()->json($listing);
        }

        return view('public/listing', [
            'agent' => $user,
            'listing' => $listing,
        ]);
    }
}
