<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use App\Models\Resources\ListingCollection;
use App\Repositories\ListingRepository;
use Illuminate\Contracts\View\View;

class AgentsController extends Controller
{
    public function detail(TelegramUser $telegramUser): View
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

        return view('Public/Agent', [
            'agent' => $telegramUser,
            'listings' => $listings,
        ]);
    }
}
