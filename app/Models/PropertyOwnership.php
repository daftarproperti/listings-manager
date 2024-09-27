<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'shm'
)]
/**
 * Property ownership/certificate
 */
enum PropertyOwnership: string
{
    case Unknown = 'unknown';
    case SHM = 'shm';
    case HGB = 'hgb';
    case Strata = 'strata';
    case Girik = 'girik';

    public static function sanitize(string $value): string
    {
        $lowercase = $value ? str_replace(' ', '', strtolower($value)) : 'unknown';
        try {
            self::from($lowercase);
            return $lowercase;
        } catch (\ValueError) {
            return 'unknown';
        }
    }
}
