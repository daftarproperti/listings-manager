<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
    example: 'east'
)]
/**
 * Facing Direction
 */
enum FacingDirection: string
{
    case Unknown = 'unknown';
    case North = 'north';
    case East = 'east';
    case South = 'south';
    case West = 'west';
    case NorthEast = 'northeast';
    case SouthEast = 'southeast';
    case SouthWest = 'southwest';
    case NorthWest = 'northwest';

    public static function sanitize(string $value): string
    {
        $lowercase = $value ? str_replace(' ', '', strtolower($value)) : 'unknown';
        try {
            self::from($lowercase);
            return $lowercase;
        } catch (\ValueError $e) {
            switch ($lowercase) {
                case 'utara':
                    return 'north';
                case 'timur':
                    return 'east';
                case 'selatan':
                    return 'south';
                case 'barat':
                    return 'west';

                case 'timurlaut':
                case 'utaratimur':
                case 'timurutara':
                    return 'northeast';

                case 'timurselatan':
                case 'selatantimur':
                case 'tenggara':
                    return 'southeast';

                case 'baratselatan':
                case 'selatanbarat':
                case 'baratdaya':
                    return 'southwest';

                case 'baratutara':
                case 'utarabarat':
                case 'baratlaut':
                    return 'northwest';

                default:
                    return 'unknown';
            }
        }
    }
}
