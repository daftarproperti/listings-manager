<?php

namespace App\Models;

enum ListingType: string
{
    case Unknown = 'unknown'; // Unknown
    case Sale = 'sale'; // Listing untuk properti dijual
    case Rent = 'rent'; // Listing untuk properti disewakan
}
