<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\ListingsController as ApiListingsController;
use App\Helpers\ListingHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListingRequest;
use App\Models\AdminNote;
use App\Models\Enums\VerifyStatus;
use App\Models\Enums\ActiveStatus;
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

    public function show(string|int $listingId): Response
    {
        $listing = ListingHelper::getListingByIdOrListingId($listingId);

        if (is_null($listing)) {
            abort(404);
        }

        $resourceData = new ListingResource($listing);

        $listingController = new ApiListingsController();

        $response = $listingController->getLikelyConnected($listing);

        $responseData = (array) $response->getData(true);
        $connectedListings = isset($responseData['connectedListings']) && is_array($responseData['connectedListings'])
            ? $responseData['connectedListings']
        : [];

        return Inertia::render('Admin/Listings/Detail/index', [
            'data' => [
                'listing' => $resourceData->resolve(),
                'likelyConnectedListing' => $connectedListings,
                'verifyStatusOptions' => VerifyStatus::options(),
                'activeStatusOptions' => ActiveStatus::options()
            ]
        ]);
    }

    public function update(string|int $listingId, ListingRequest $request): RedirectResponse
    {
        $listing = ListingHelper::getListingByIdOrListingId($listingId);
        if (is_null($listing)) {
            abort(404);
        }

        $data = $request->validated();
        $adminNoteMessage = $data['adminNote'] ?? '';
        $adminNote = [
            'message' => $adminNoteMessage,
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
