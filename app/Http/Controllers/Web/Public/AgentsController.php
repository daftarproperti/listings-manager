<?php

namespace App\Http\Controllers\Web\Public;
use Illuminate\Http\Request;


use App\Http\Controllers\Controller;
use App\Models\FilterSet;
use App\Models\TelegramUser;
use App\Models\Resources\ListingCollection;
use App\Repositories\ListingRepository;
use Illuminate\Contracts\View\View;

class AgentsController extends Controller
{
    public function detail(TelegramUser $telegramUser, Request $request): View
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
            'price' => $request->query('price'),
            'propertyType' => $request->query('propertyType'),
            'bedroomCount' => $request->query('bedroomCount'),
            'bathroomCount' => $request->query('bathroomCount'),
            'lotSize' => $request->query('lotSize'),
            'buildingSize' => $request->query('buildingSize'),
            'ownership' => $request->query('ownership'),
            'carCount' => $request->query('carCount'),
            'electricPower' => $request->query('electricPower'),
            'city' => $request->query('city'),
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
