<?php

namespace App\Models\Enums;

use App\Traits\EnumOptions;
use App\Traits\EnumValues;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'sold'
)]
/**
 * Closing Type
 */
enum ClosingType: string
{
    use EnumValues;
    use EnumOptions;

    case SOLD = 'sold';
    case RENTED = 'rented';

    public function display(): string
    {
        return match ($this) {
            self::SOLD => 'Terjual',
            self::RENTED => 'Disewa',
        };
    }
}
