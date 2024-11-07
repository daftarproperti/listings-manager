<?php

namespace App\Http\Middleware;

use App\Helpers\DPAuth;
use Closure;
use Illuminate\Http\Request;

class ListingUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\Listing $listing */
        $listing = $request->listing;

        $isMyListing = false;

        $user = DPAuth::appUser();
        if ($listing->user && ($listing->user->userId == $user->user_id)) {
            $isMyListing = true;
        }

        if (!$isMyListing) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
