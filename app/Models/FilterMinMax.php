<?php

namespace App\Models;

use Spatie\LaravelData\Data;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FilterMinMax',
    type: 'object',
    description: 'Filter Min Max'
)]
class FilterMinMax extends Data
{
    #[OA\Property(property: 'min', type: 'integer', nullable: true, description: 'Minimum value')]
    public ?int $min;

    #[OA\Property(property: 'max', type: 'integer', nullable: true, description: 'Maximum value')]
    public ?int $max;
}
