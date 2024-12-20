<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AiReviewResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\AiReview $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'listingId' => $prop->listing->listingId,
            'results' => $prop->results,
            'streetViewImages' => $prop->streetViewImages ?? [],
            'verifiedImageUrls' => $prop->verifiedImageUrls ?? [],
            'status' => $prop->status,
            'updatedAt' => $prop->updated_at->toIso8601ZuluString(),
        ];
    }
}
