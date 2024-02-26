<?php

namespace App\Http\Middleware;

use App\Models\TelegramUser;
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

        if (!$listing->user || ($listing->user->userId !== app(TelegramUser::class)->user_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
