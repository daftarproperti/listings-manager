<?php

namespace App\Http\Controllers\Web\Public;
use Illuminate\Http\Request;


use App\Http\Controllers\Controller;
use App\Models\FilterSet;
use App\Models\User;
use App\Models\Resources\ListingCollection;
use App\Repositories\ListingRepository;
use Illuminate\Contracts\View\View;

class AgentsController extends Controller
{
    public function detail(User $user, Request $request): View
    {
        if(!isset($user->isPublicProfile) || !$user->isPublicProfile) {
            abort(404, 'User not found');
        }

        $filterSet = FilterSet::from([
            'collection' => true,
            'userId' => $user->user_id,
            'sort' => 'created_at',
            'order' => 'desc',
            'price' => $request->query('price'),
            'rentPrice' => $request->query('rentPrice'),
            'propertyType' => $request->query('propertyType'),
            'listingForSale' => $request->query('listingForSale'),
            'listingForRent' => $request->query('listingForRent'),
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
            'agent' => $user,
            'listings' => $listings,
        ]);
    }
}
