<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="ListingSort",
 *     type="string",
 *     description="Sort Listing By",
 *     enum={"price", "bedroomCount", "bathroomCount", "lotSize", "buildingSize"},
 *     example="price"
 * )
 */
enum ListingSort: string
{
    case Price = 'price'; // Harga
    case BedroomCount = 'bedroomCount'; // Kamar Tidur
    case BathroomCount = 'bathroomCount'; // Kamar Mandi
    case LotSize = 'lotSize'; // Luas Tanah
    case BuildingSize = 'buildingSize'; // Luas Bangunan
}
