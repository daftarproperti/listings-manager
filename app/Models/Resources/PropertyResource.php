<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Property",
 *     type="object",
 *)
 */
class PropertyResource extends JsonResource
{
    public static $wrap = null;
    /**
     * @OA\Property(property="id",type="string")
     * @OA\Property(property="title",type="string")
     * @OA\Property(property="address",type="string")
     * @OA\Property(property="description",type="string")
     * @OA\Property(property="price",type="integer")
     * @OA\Property(property="lotSize",type="integer")
     * @OA\Property(property="buildingSize",type="integer")
     * @OA\Property(property="carCount",type="integer")
     * @OA\Property(property="bedroomCount",type="integer")
     * @OA\Property(property="bathroomCount",type="integer")
     * @OA\Property(property="floorCount",type="integer")
     * @OA\Property(property="electricPower",type="integer")
     * @OA\Property(property="facing",type="string")
     * @OA\Property(property="ownership",type="string")
     * @OA\Property(property="city",type="string")
     * @OA\Property(property="pictureUrls",type="array",@OA\Items(type="string", format="uri", example="https://example.com/image.jpg"))
     * @OA\Property(property="coordinate",type="object",
     *      @OA\Property(property="latitude",type="integer"),
     *      @OA\Property(property="longitude",type="integer")
     * )
     * @OA\Property(property="contacts",type="object",
     *      @OA\Property(property="name",type="string"),
     *      @OA\Property(property="profilePictureURL",type="string"),
     *      @OA\Property(property="phoneNumber",type="string"),
     *      @OA\Property(property="sourceURL",type="string"),
     *      @OA\Property(property="provider",type="string")
     * )
     * @OA\Property(property="userCanEdit",type="boolean")
     * @OA\Property(property="isPrivate",type="boolean")
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'address' => $this->address,
            'description' => $this->description,
            'price' => $this->price ? (int) $this->price : null,
            'lotSize' => $this->lotSize ? (int) $this->lotSize : null,
            'buildingSize' => $this->buildingSize ? (int) $this->buildingSize : null,
            'carCount' => $this->carCount ? (int) $this->carCount : null,
            'bedroomCount' => $this->bedroomCount ? (int) $this->bedroomCount : null,
            'bathroomCount' => $this->bathroomCount ? (int) $this->bathroomCount : null,
            'floorCount' => $this->floorCount ? (int) $this->floorCount : null,
            'electricPower' => $this->electricPower ? (int) $this->electricPower : null,
            'facing' => $this->facing,
            'ownership' => $this->ownership,
            'city' => $this->city,
            'pictureUrls' => $this->pictureUrls,
            'coordinate' => [
                'latitude' => $this->latitude,
                'longitude' => $this->logitude
            ],
            'contacts' => [
                'name' => $this->contacts ? ($this->contacts['name'] ?? null) :  null,
                'phoneNumber' => $this->contacts ? ($this->contacts['phoneNumber'] ?? null) : null,
                'profilePictureURL' => $this->contacts ? ($this->contacts['profilePictureURL'] ?? null) : null,
                'sourceURL' =>  $this->contacts ? ($this->contacts['sourceURL'] ?? null) : null,
                'provider' =>  $this->contacts ? ($this->contacts['provider'] ?? null) : null,
            ],
            'userCanEdit' => $this->user_can_edit,
            'isPrivate' => $this->isPrivate ?? false
        ];
    }
}
