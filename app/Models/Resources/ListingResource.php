<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Listing",
 *     type="object",
 *)
 */
class ListingResource extends JsonResource
{
    public static $wrap = null;
    /**
     * @OA\Property(property="id",type="string")
     * @OA\Property(property="sourceText",type="string")
     * @OA\Property(property="title",type="string")
     * @OA\Property(property="propertyType",ref="#/components/schemas/PropertyType")
     * @OA\Property(property="listingType",ref="#/components/schemas/ListingType")
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
     * @OA\Property(property="viewCount",type="integer")
     * @OA\Property(property="matchFilterCount",type="integer")
     * @OA\Property(property="facing",type="string")
     * @OA\Property(property="ownership",ref="#/components/schemas/PropertyOwnership")
     * @OA\Property(property="city",type="string")
     * @OA\Property(property="pictureUrls",type="array",@OA\Items(type="string", format="uri", example="https://example.com/image.jpg"))
     * @OA\Property(property="coordinate",type="object",
     *      @OA\Property(property="latitude",type="integer"),
     *      @OA\Property(property="longitude",type="integer")
     * )
     * @OA\Property(property="contact",type="object",
     *      @OA\Property(property="name",type="string"),
     *      @OA\Property(property="phoneNumber",type="string"),
     *      @OA\Property(property="company",type="string")
     * )
     * @OA\Property(property="user",type="object",
     *      @OA\Property(property="name",type="string"),
     *      @OA\Property(property="profilePictureURL",type="string"),
     *      @OA\Property(property="phoneNumber",type="string"),
     * )
     * @OA\Property(property="userCanEdit",type="boolean")
     * @OA\Property(property="isPrivate",type="boolean")
     * @OA\Property(property="updatedAt", type="string", format="date-time")
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\Listing $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'sourceText' => $prop->sourceText,
            'title' => $prop->title,
            'propertyType' => $prop->propertyType,
            'listingType' => $prop->listingType,
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
            'viewCount' => $prop->viewCount,
            'matchFilterCount' => $prop->matchFilterCount,
            'facing' => $prop->facing,
            'ownership' => $prop->ownership,
            'city' => $prop->city,
            'pictureUrls' => $prop->pictureUrls,
            'coordinate' => [
                'latitude' => $prop->latitude,
                'longitude' => $prop->longitude,
            ],
            'contact' => [
                'name' => $prop->contact ? ($prop->contact['name'] ?? null) :  null,
                'phoneNumber' => $prop->contact ? ($prop->contact['phoneNumber'] ?? null) : null,
                'company' =>  $prop->contact ? ($prop->contact['company'] ?? null) : null,
            ],
            'user' => [
                'name' => $prop->user_profile ? $prop->user_profile->name : null,
                'phoneNumber' => $prop->user_profile ? $prop->user_profile->phoneNumber : null,
                'city' => $prop->user_profile ? $prop->user_profile->city : null,
                'profilePictureURL' => $prop->user_profile ? $prop->user_profile->picture : null,
                'company' => $prop->user_profile ? $prop->user_profile->company : null,
                'description' => $prop->user_profile ? $prop->user_profile->description : null,
            ],
            'userCanEdit' => $prop->user_can_edit,
            'isPrivate' => $prop->isPrivate ?? false,
            'updatedAt' => $prop->updated_at ? $prop->updated_at->isoFormat('D MMMM YYYY') : null,
        ];
    }
}
