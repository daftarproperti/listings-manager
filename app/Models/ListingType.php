<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="ListingType",
 *     type="string",
 *     description="Listing type",
 *     enum={"unknown", "sale", "rent"},
 *     example="house"
 * )
 */
enum ListingType: string
{
    case Unknown = 'unknown'; // Unknown
    case Sale = 'sale'; // Listing untuk properti dijual
    case Rent = 'rent'; // Listing untuk properti disewakan
}
