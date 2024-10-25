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
use App\Models\Resources\ListingHistoryResource;
use App\Models\Resources\ListingResource;
use App\Repositories\Admin\ListingRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
            'sortOrder',
        ]);

        $listings = $repository->list($input);

        $listingCollection = new ListingCollection($listings);

        return Inertia::render('Admin/Listings/index', [
            'data' => [
                'listings' => $listingCollection->collection,
                'lastPage' => $listings->lastPage(),
                'verifyStatusOptions' => VerifyStatus::options(),
                'activeStatusOptions' => ActiveStatus::options(),
            ],
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

        $rawListingHistory = $listing->listingHistories()->orderBy('created_at', 'desc')->get();
        $listingHistories = ListingHistoryResource::collection($rawListingHistory);

        $adminAttention = $listing->adminAttentions?->first();

        return Inertia::render('Admin/Listings/Detail/index', [
            'data' => [
                'listing' => $resourceData->resolve(),
                'listingHistory' => $listingHistories->resolve(),
                'likelyConnectedListing' => $connectedListings,
                'verifyStatusOptions' => VerifyStatus::options(),
                'activeStatusOptions' => ActiveStatus::options(),
                'adminAttention' => $adminAttention,
            ],
        ]);
    }

    public function update(string|int $listingId, ListingRequest $request): RedirectResponse
    {
        $listing = ListingHelper::getListingByIdOrListingId($listingId);
        if (is_null($listing)) {
            abort(404);
        }

        $data = $request->validated();
        $lock = Cache::lock('update-listing-' . $listing->id, 4);
        try {
            $lock->block(4);
            if (isset($data['revision'])) {
                if ($listing->revision !== null && $listing->revision !== $data['revision']) {
                    return Redirect::back()->withErrors([
                        'error' => 'Data Listing telah diubah. Mohon muat ulang halaman ini.',
                    ]);
                }
            }

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

            if ($listing->isDirty()) {
                $listing->revision = (isset($listing->revision) ? (int)$listing->revision : 0) + 1;
            }

            $listing->save();
        } catch (LockTimeoutException) {
            Log::error('Could not acquire lock update-listing');
        } finally {
            $lock->release();
        }

        return Redirect::to($request->url());
    }

    public function removeAttention(string|int $listingId): RedirectResponse
    {
        $listing = ListingHelper::getListingByIdOrListingId($listingId);
        if (is_null($listing)) {
            abort(404);
        }

        $listing->adminAttentions()->delete();

        return Redirect::back()->with('success', 'Atensi berhasil dihapus.');
    }
}
