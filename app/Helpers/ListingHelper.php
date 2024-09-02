<?php

namespace App\Helpers;

use App\Models\Listing;

class ListingHelper
{
    /**
     * @param string|int $listingId
     * @return Listing|null
     */
    public static function getListingByIdOrListingId(string|int $listingId): ?Listing
    {
        $result = null;
        if (is_numeric($listingId)) {
            $result = Listing::where('listingId', intval($listingId))->first();
        } else {
            $result = Listing::find($listingId);
        }

        /** @var Listing|null $result */
        return $result;
    }
}
