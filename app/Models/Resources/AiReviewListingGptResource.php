<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class AiReviewListingGptResource extends JsonResource
{

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\Listing $prop */
        $prop = $this->resource;

        return [
            'title' => $prop->title,
            'description' => $prop->description,
            'address' => $prop->address,
            'price' => $prop->price ??  null,
            'rentPrice' => $prop->rentPrice ?? null,
            'lotSize' => $prop->lotSize ?? null,
            'buildingSize' => $prop->buildingSize ?? null,
            'carCount' => $prop->carCount ?? null,
            'bedroomCount' => $prop->bedroomCount ?? null,
            'additionalBedroomCount' => $prop->additionalBedroomCount ?? null,
            'bathroomCount' => $prop->bathroomCount ?? null,
            'additionalBathroomCount' => $prop->additionalBathroomCount ?? null,
            'floorCount' => $prop->floorCount ?? null,
            'electricPower' => $prop->electricPower ?? null,
            'facing' => $prop->facing,
            'ownership' => $prop->ownership,
            'city' => $prop->city ?? ''
        ];
    }
}
