<?php

namespace App\Models\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SavedSearch',
    type: 'object'
)]
class SavedSearchResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'string')]
    #[OA\Property(property: 'userId', type: 'integer')]
    #[OA\Property(property: 'title', type: 'string')]
    #[OA\Property(property: 'filterSet', ref: '#/components/schemas/FilterSet')]

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\SavedSearch $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'userId' => $prop->userId,
            'title' => $prop->title,
            'filterSet' => $prop->filterSet,
        ];
    }
}
