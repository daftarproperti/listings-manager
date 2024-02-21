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
     * @OA\Property(property="user",type="object",
     *      @OA\Property(property="name",type="string"),
     *      @OA\Property(property="profilePictureURL",type="string"),
     *      @OA\Property(property="phoneNumber",type="string"),
     * )
     * @OA\Property(property="userCanEdit",type="boolean")
     * @OA\Property(property="isPrivate",type="boolean")
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\Property $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'title' => $prop->title,
            'address' => $prop->address,
            'description' => $prop->description,
            'price' => $prop->price ? (int) $prop->price : null,
            'lotSize' => $prop->lotSize ? (int) $prop->lotSize : null,
            'buildingSize' => $prop->buildingSize ? (int) $prop->buildingSize : null,
            'carCount' => $prop->carCount ? (int) $prop->carCount : null,
            'bedroomCount' => $prop->bedroomCount ? (int) $prop->bedroomCount : null,
            'bathroomCount' => $prop->bathroomCount ? (int) $prop->bathroomCount : null,
            'floorCount' => $prop->floorCount ? (int) $prop->floorCount : null,
            'electricPower' => $prop->electricPower ? (int) $prop->electricPower : null,
            'facing' => $prop->facing,
            'ownership' => $prop->ownership,
            'city' => $prop->city,
            'pictureUrls' => $prop->pictureUrls,
            'coordinate' => [
                'latitude' => $prop->latitude,
                'longitude' => $prop->longitude,
            ],
            'contacts' => [
                'name' => $prop->contacts ? ($prop->contacts['name'] ?? null) :  null,
                'phoneNumber' => $prop->contacts ? ($prop->contacts['phoneNumber'] ?? null) : null,
                'profilePictureURL' => $prop->contacts ? ($prop->contacts['profilePictureURL'] ?? null) : null,
                'sourceURL' =>  $prop->contacts ? ($prop->contacts['sourceURL'] ?? null) : null,
                'provider' =>  $prop->contacts ? ($prop->contacts['provider'] ?? null) : null,
            ],
            'user' => [
                'name' => $prop->user_profile ? $prop->user_profile->name : null,
                'phoneNumber' => $prop->user_profile ? $prop->user_profile->phoneNumber : null,
                'profilePictureURL' => $prop->user_profile ? $prop->user_profile->picture : null
            ],
            'userCanEdit' => $prop->user_can_edit,
            'isPrivate' => $prop->isPrivate ?? false
        ];
    }
}
