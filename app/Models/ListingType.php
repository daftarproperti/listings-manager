<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: "string",
    example: "house"
)]
/**
 * Listing type
 */
enum ListingType: string
{
    case Unknown = 'unknown'; // Unknown
    case Sale = 'sale'; // Listing untuk properti dijual
    case Rent = 'rent'; // Listing untuk properti disewakan
}
