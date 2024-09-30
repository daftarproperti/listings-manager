<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resources\ListingCollection;
use App\Repositories\Admin\ListingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpiredListingsController extends Controller
{
    public function index(Request $request, ListingRepository $repository): RedirectResponse|Response
    {
        $input = $request->only([
            'q',
            'sortBy',
            'sortOrder',
        ]);

        $listing = $repository->listWithExpiredDate($input);
        $listingCollection = new ListingCollection($listing);

        return Inertia::render('Admin/ExpiredListings/index', [
            'data' => [
                'listings' => $listingCollection->collection,
                'lastPage' => $listing->lastPage(),
            ],
        ]);
    }
}
