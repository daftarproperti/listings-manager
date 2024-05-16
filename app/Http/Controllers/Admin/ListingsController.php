<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enums\VerifyStatus;
use App\Models\Listing;
use App\Models\Resources\ListingCollection;
use App\Models\Resources\ListingResource;
use App\Repositories\Admin\ListingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingsController extends Controller
{
    public function index(Request $request, ListingRepository $repository): RedirectResponse|Response
    {
        $input = $request->only([
            'q',
            'verifyStatus'
        ]);

        $listing = $repository->list($input);
        $listingCollection = new ListingCollection($listing);

        if (!isset($input['verifyStatus'])) {
            return redirect()->route('listing.index', ['verifyStatus' => VerifyStatus::ON_REVIEW]);
        }

        return Inertia::render('Admin/Listings/index', [
            'data' => [
                'listings' => $listingCollection->collection,
                'lastPage' => $listing->lastPage(),
                'verifyStatusOptions' => VerifyStatus::options()
            ]
        ]);
    }

    public function show(Listing $listing): Response
    {
        $resourceData = new ListingResource($listing);

        return Inertia::render('Admin/Listings/Detail/index', [
            'data' => [
                'listing' => $resourceData->resolve()
            ]
        ]);
    }
}
