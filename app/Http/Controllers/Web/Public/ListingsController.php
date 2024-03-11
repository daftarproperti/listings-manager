<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Inertia\Inertia;
use Inertia\Response;

class ListingsController extends Controller
{
    public function detail(Listing $listing): Response
    {
        return Inertia::render('Public/Listing', [
            'listing' => $listing,
        ]);
    }
}
