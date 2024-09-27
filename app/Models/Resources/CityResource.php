<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\City;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "City",
    type: "object"
)]
class CityResource extends JsonResource
{
    #[OA\Property(property: "id", type: "integer")]
    #[OA\Property(property: "name", type: "string")]
    #[OA\Property(property: "latitude", type: "integer")]
    #[OA\Property(property: "longitude", type: "integer")]

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var City $city */
        $city = $this->resource;

        return [
            'id' => $city->osmId,
            'name' => $city->displayName,
            'latitude' => $city->latitude,
            'longitude' => $city->longitude
        ];
    }
}
