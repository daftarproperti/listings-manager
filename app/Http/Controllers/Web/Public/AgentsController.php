<?php

namespace App\Http\Controllers\Web\Public;

use App\DTO\FilterSet;
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

        $filterSet = FilterSet::from([
            'collection' => true,
            'userId' => $telegramUser->user_id,
            'sort' => 'created_at',
            'order' => 'desc',
        ]);

        $listingRepo = new ListingRepository();
        $listingCollections = new ListingCollection($listingRepo->list($filterSet));
        $listings = $listingCollections->collection;

        return view('public/agent', [
            'agent' => $telegramUser,
            'listings' => $listings,
        ]);
    }
}
