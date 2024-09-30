<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resources\ListingCollection;
use App\Repositories\Admin\ListingRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Admin/Welcome');
    }

    public function dashboard(Request $request, ListingRepository $repository): Response
    {
        $input = [
            'q' => $request->input('q'),
        ];

        $listing = $repository->list($input);
        $listingCollection = new ListingCollection($listing);

        return Inertia::render('Admin/Dashboard', [
            'data' => [
                'listings' => $listingCollection->collection,
                'lastPage' => $listing->lastPage(),
            ],
        ]);
    }
}
