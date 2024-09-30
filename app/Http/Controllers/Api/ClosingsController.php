<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClosingRequest;
use App\Models\Closing;
use App\Models\Enums\ClosingStatus;
use App\Models\Listing;
use App\Models\Resources\ListingResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use MongoDB\BSON\UTCDateTime;

class ClosingsController extends Controller
{
    #[OA\Post(
        path: '/api/app/listings/{id}/closings',
        tags: ['Listings'],
        summary: 'Add a closing to a listing',
        operationId: 'listings.closing',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Listing Id',
                schema: new OA\Schema(
                    type: 'string',
                ),
            ),
        ],
        requestBody: new OA\RequestBody(
            ref: '#/components/schemas/ClosingRequest',
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Listing',
                ),
            ),
        ],
    )]
    public function closing(Listing $listing, ClosingRequest $request): JsonResource
    {
        $validated = $request->validated();

        // Convert the date string to a MongoDB UTCDateTime
        $date = Carbon::parse(type($validated['date'])->asString());

        $validated['date'] = new UTCDateTime($date->getTimestampMs());

        $closing = new Closing();
        $closing->listing = $listing;
        $closing->status = (ClosingStatus::ON_REVIEW)->value;
        $closing->fill($validated);

        $listing->closings()->save($closing);

        return new ListingResource($listing);
    }
}
