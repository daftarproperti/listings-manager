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
            'price' => $this->price,
            'lotSize' => $this->lotSize,
            'buildingSize' => $this->buildingSize,
            'carCount' => $this->carCount,
            'bedroomCount' => $this->bedroomCount,
            'bathroomCount' => $this->bathroomCount,
            'floorCount' => $this->floorCount,
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
                'profilePictureURL' => $this->contacts ? ($this->contacts['profilePictureURL'] ?? null) : null,
                'sourceURL' =>  $this->contacts ? ($this->contacts['sourceURL'] ?? null) : null,
                'provider' =>  $this->contacts ? ($this->contacts['provider'] ?? null) : null,
            ],
            'userCanEdit' => $this->user_can_edit,
            'isPrivate' => $this->isPrivate ?? false
        ];
    }
}
