<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListingRequest;
use App\Models\AdminNote;
use App\Models\Enums\VerifyStatus;
use App\Models\Enums\ActiveStatus;
use App\Models\Listing;
use App\Models\Resources\ListingCollection;
use App\Models\Resources\ListingResource;
use App\Repositories\Admin\ListingRepository;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ListingsController extends Controller
{
    public function index(Request $request, ListingRepository $repository): RedirectResponse|Response
    {
        $input = $request->only([
            'q',
            'verifyStatus',
            'activeStatus',
            'sortBy',
            'sortOrder'
        ]);

        $listing = $repository->list($input);
        $listingCollection = new ListingCollection($listing);

        return Inertia::render('Admin/Listings/index', [
            'data' => [
                'listings' => $listingCollection->collection,
                'lastPage' => $listing->lastPage(),
                'verifyStatusOptions' => VerifyStatus::options(),
                'activeStatusOptions' => ActiveStatus::options()
            ]
        ]);
    }

    public function show(Listing $listing): Response
    {
        $resourceData = new ListingResource($listing);

        return Inertia::render('Admin/Listings/Detail/index', [
            'data' => [
                'listing' => $resourceData->resolve(),
                'verifyStatusOptions' => VerifyStatus::options(),
                'activeStatusOptions' => ActiveStatus::options()
            ]
        ]);
    }

    public function update(Listing $listing, ListingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $adminNote = [
            'message' => $data['adminNote'],
            'email' => Auth::user()?->email,
            'date' => Carbon::now(),
        ];
        $data['adminNote'] = AdminNote::from($adminNote);

        foreach ($data as $key => $value) {
            $listing->{$key} = $value;
        };
        $listing->save();

        return Redirect::to($request->url());
    }
}
