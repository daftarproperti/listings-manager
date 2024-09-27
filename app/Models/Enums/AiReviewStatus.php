<?php

namespace App\Models\Enums;

use App\Traits\EnumOptions;
use App\Traits\EnumValues;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'done'
)]
/**
 * AiReview Status
 */
enum AiReviewStatus: string
{
    use EnumValues;
    use EnumOptions;

    case PROCESSING = 'processing';
    case DONE = 'done';

    public function display(): string
    {
        return match ($this) {
            self::PROCESSING => 'Sedang Diproses',
            self::DONE => 'Selesai Diproses',
        };
    }
}
