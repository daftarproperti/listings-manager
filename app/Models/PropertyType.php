<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="PropertyType",
 *     type="string",
 *     description="Property type",
 *     enum={"unknown", "house", "apartment", "warehouse", "shophouse", "land", "villa"},
 *     example="house"
 * )
 */
enum PropertyType: string
{
    case Unknown = 'unknown'; // Unknown
    case House = 'house'; // Rumah
    case Apartment = 'apartment'; // Apartemen
    case Warehouse = 'warehouse'; // Gudang
    case Shophouse = 'shophouse'; // Ruko
    case Land = 'land'; // Tanah
    case Villa = 'villa'; // Villa
}
