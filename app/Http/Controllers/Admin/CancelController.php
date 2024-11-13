<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ListingHelper;
use App\Http\Requests\Admin\ListingRequest;
use App\Http\Controllers\Controller;
use App\Models\CancellationNote;
use App\Models\Enums\CancellationStatus;
use App\Models\Listing;
use App\Models\Resources\ListingCollection;
use App\Models\Resources\ListingResource;
use App\Repositories\Admin\ListingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CancelController extends Controller
{
    public function index(Request $request, ListingRepository $repository): RedirectResponse|Response
    {
        $input = $request->only([
            'q',
            'sortBy',
            'sortOrder',
        ]);

        $listing = $repository->listWithCancellationNote($input);
        $listingCollection = new ListingCollection($listing);

        return Inertia::render('Admin/Cancel/index', [
            'data' => [
                'listings' => $listingCollection->collection,
                'cancellationStatusOptions' => CancellationStatus::options(),
                'lastPage' => $listing->lastPage(),
                'totalListings' => $listing->total(),
            ],
        ]);
    }

    public function show(Listing $listing): Response
    {
        $resourceData = new ListingResource($listing);

        return Inertia::render('Admin/Cancel/Form', [
            'data' => [
                'listing' => $resourceData->resolve(),
                'cancellationStatusOptions' => CancellationStatus::options(),
            ],
        ]);
    }

    public function update(string|int $listingId, ListingRequest $request): RedirectResponse
    {
        $listing = ListingHelper::getListingByIdOrListingId($listingId);
        if (is_null($listing)) {
            abort(404);
        }

        $data = $request->validate([
            'reason' => 'required|string',
            'status' => 'required|string',
        ]);

        $cancellationNote = [
            'reason' => $data['reason'] ?? '',
            'status' => CancellationStatus::from($data['status']),
        ];

        $data['cancellationNote'] = CancellationNote::from($cancellationNote);

        $listing->cancellationNote = CancellationNote::from($data['cancellationNote']);
        $listing->save();

        return Redirect::to($request->url());
    }
}
