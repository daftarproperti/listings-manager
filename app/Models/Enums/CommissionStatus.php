<?php

namespace App\Models\Enums;

use App\Traits\EnumOptions;
use App\Traits\EnumValues;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'paid'
)]
/**
 * Commission Status
 */
enum CommissionStatus: string
{
    use EnumValues;
    use EnumOptions;

    case PENDING = 'pending';
    case PAID = 'paid';
    case UNPAID = 'unpaid';

    public function display(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Komisi',
            self::PAID => 'Komisi Dibayarkan',
            self::UNPAID => 'Komisi Belum Dibayarkan',
        };
    }
}
