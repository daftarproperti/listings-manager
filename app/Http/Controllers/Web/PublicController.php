<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\TelegramUser;
use App\Models\Resources\ListingCollection;
use App\Repositories\ListingRepository;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    public function publicListingPage(Listing $listing): Response
    {
        return Inertia::render('Public/Listing', [
            'listing' => $listing,
        ]);
    }

    public function publicAgentPage(TelegramUser $telegramUser): Response
    {
        $userProfile = $telegramUser->profile;
        if(!$userProfile || !isset($userProfile->isPublicProfile) || !$userProfile->isPublicProfile) {
            abort(404, 'User not found');
        }

        $filter = [
            'collection' => true,
            'userId' => $telegramUser->user_id
        ];

        $listingRepo = new ListingRepository();
        $listingCollections = new ListingCollection($listingRepo->list($filter));
        $listings = $listingCollections->collection;

        return Inertia::render('Public/Agent', [
            'agent' => $telegramUser,
            'listings' => $listings,
        ]);
    }
}
