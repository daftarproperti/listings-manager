<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enums\VerifyStatus;
use App\Models\Enums\ActiveStatus;
use App\Models\Resources\ListingCollection;
use App\Repositories\Admin\ListingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingsWithAttentionController extends Controller
{
    public function index(Request $request, ListingRepository $repository): RedirectResponse|Response
    {
        $input = $request->only([
            'q',
            'verifyStatus',
            'activeStatus',
        ]);

        $input['sortBy'] = $request->input('sortBy', 'updated_at');
        $input['sortOrder'] = $request->input('sortOrder', 'desc');

        $listings = $repository->list($input, true);
        $listingCollection = new ListingCollection($listings);

        return Inertia::render('Admin/ListingsWithAttention/index', [
            'data' => [
                'listings' => $listingCollection->collection,
                'lastPage' => $listings->lastPage(),
                'totalListings' => $listings->total(),
                'verifyStatusOptions' => VerifyStatus::options(),
                'activeStatusOptions' => ActiveStatus::options(),
            ],
        ]);
    }
}
