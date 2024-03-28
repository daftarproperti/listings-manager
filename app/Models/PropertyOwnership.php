<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="PropertyOwnership",
 *     type="string",
 *     description="Property ownership/certificate",
 *     enum={"unknown", "shm", "hgb", "strata", "girik"},
 *     example="shm"
 * )
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
        $lowercase = $value ? str_replace(' ', '', strtolower($value)) : "unknown";
        try {
            self::from($lowercase);
            return $lowercase;
        } catch (\ValueError $e) {
            return "unknown";
        }
    }
}
