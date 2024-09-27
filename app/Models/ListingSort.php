<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'price'
)]
/**
 * Sort Listing By
 */
enum ListingSort: string
{
    case Price = 'price'; // Harga
    case BedroomCount = 'bedroomCount'; // Kamar Tidur
    case BathroomCount = 'bathroomCount'; // Kamar Mandi
    case LotSize = 'lotSize'; // Luas Tanah
    case BuildingSize = 'buildingSize'; // Luas Bangunan
}
