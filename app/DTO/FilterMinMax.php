<?php

namespace App\DTO;

use Spatie\LaravelData\Dto;

/**
 * @OA\Schema(
 *     schema="FilterMinMax",
 *     type="object",
 *     description="Filter Min Max DTO",
 *     @OA\Property(property="min", type="integer", nullable=true, description="Minimum value"),
 *     @OA\Property(property="max", type="integer", nullable=true, description="Maximum value"),
 * )
 */
class FilterMinMax extends Dto {
    public ?int $min;
    public ?int $max;
}