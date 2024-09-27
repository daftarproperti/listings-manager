<?php

namespace App\Models\Enums;

use App\Traits\EnumOptions;
use App\Traits\EnumValues;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'on_review'
)]
enum CancellationStatus: string
{
    use EnumValues;
    use EnumOptions;

    case ON_REVIEW = 'on_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function display(): string
    {
        return match ($this) {
            self::ON_REVIEW => 'Sedang Ditinjau',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }
}
