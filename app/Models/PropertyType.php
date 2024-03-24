<?php

namespace App\Models;

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