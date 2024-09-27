<?php

namespace App\Models;

use Spatie\LaravelData\Data;
use OpenApi\Attributes as OA;
use App\Models\Enums\CancellationStatus;

#[OA\Schema(
    type: 'object',
    properties: [
        new OA\Property(property: 'reason', type: 'string', example: 'Hello World'),
        new OA\Property(property: 'status', type: 'string', example: 'on_review'),
    ]
)]
class CancellationNote extends Data
{
    public function __construct(
        public string $reason = '',
        public CancellationStatus $status = CancellationStatus::ON_REVIEW
    ) {
    }
}
