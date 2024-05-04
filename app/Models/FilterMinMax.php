<?php

namespace App\Models;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="FilterMinMax",
 *     type="object",
 *     description="Filter Min Max",
 *     @OA\Property(property="min", type="integer", nullable=true, description="Minimum value"),
 *     @OA\Property(property="max", type="integer", nullable=true, description="Maximum value"),
 * )
 */
class FilterMinMax extends Data {
    public ?int $min;
    public ?int $max;
}
