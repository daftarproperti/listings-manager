<?php

namespace App\Models\Enums;

use App\Traits\EnumOptions;
use App\Traits\EnumValues;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: "string",
    example: "waitlisted"
)]
enum ActiveStatus: string
{
    use EnumValues;
    use EnumOptions;

    case WAITLISTED = 'waitlisted';
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';

    public function display(): string
    {
        return match ($this) {
            self::WAITLISTED => 'Dalam Antrian',
            self::ACTIVE => 'Aktif',
            self::ARCHIVED => 'Diarsipkan',
        };
    }
}
