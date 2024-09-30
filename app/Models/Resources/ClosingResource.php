<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Closing;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Closing',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '6asdasd'),
        new OA\Property(property: 'listingId', type: 'string', example: '15000'),
        new OA\Property(property: 'closingType', ref: '#/components/schemas/ClosingType'),
        new OA\Property(property: 'clientName', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'clientPhoneNumber', type: 'string', example: '+6281234567890'),
        new OA\Property(property: 'transactionValue', type: 'integer', example: 100000),
        new OA\Property(property: 'date', type: 'string', format: 'date-time', example: '2024-03-01T23:00:00+00:00'),
        new OA\Property(property: 'notes', type: 'string', example: 'Notes'),
        new OA\Property(
            property: 'status',
            type: 'string',
            example: 'on_review',
            ref: '#/components/schemas/ClosingStatus',
        ),
        new OA\Property(
            property: 'commissionStatus',
            type: 'string',
            example: 'pending',
            ref: '#/components/schemas/CommissionStatus',
        ),
    ],
)]
class ClosingResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var Closing $closing */
        $closing = $this->resource;
        $date = Carbon::createFromTimestamp($closing->date->toDateTime()->getTimestamp());

        return [
            'id' => $closing->id,
            'listingId' => (string) $closing->listing->listingId,
            'closingType' => $closing->closingType,
            'clientName' => $closing->clientName,
            'clientPhoneNumber' => $closing->clientPhoneNumber,
            'transactionValue' => $closing->transactionValue,
            'date' => $date->toIso8601ZuluString(),
            'status' => $closing->status,
            'commissionStatus' => $closing->commissionStatus,
            'notes' => $closing->notes,
        ];
    }
}
