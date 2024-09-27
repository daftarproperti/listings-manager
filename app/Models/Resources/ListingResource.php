<?php

namespace App\Models\Resources;

use App\Helpers\Photo;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Listing",
    type: "object"
)]
class ListingResource extends JsonResource
{
    #[OA\Property(property: "id", type: "string")]
    #[OA\Property(property: "listingId", type: "integer")]
    #[OA\Property(property: "listingIdStr", type: "string")]
    #[OA\Property(property: "sourceText", type: "string")]
    #[OA\Property(property: "title", type: "string")]
    #[OA\Property(property: "propertyType", ref: "#/components/schemas/PropertyType")]
    #[OA\Property(property: "listingType", ref: "#/components/schemas/ListingType")]
    #[OA\Property(property: "listingForSale", type: "boolean")]
    #[OA\Property(property: "listingForRent", type: "boolean")]
    #[OA\Property(property: "address", type: "string")]
    #[OA\Property(property: "description", type: "string")]
    #[OA\Property(property: "price", type: "integer")]
    #[OA\Property(property: "rentPrice", type: "integer")]
    #[OA\Property(property: "lotSize", type: "integer")]
    #[OA\Property(property: "buildingSize", type: "integer")]
    #[OA\Property(property: "carCount", type: "integer")]
    #[OA\Property(property: "bedroomCount", type: "integer")]
    #[OA\Property(property: "additionalBedroomCount", type: "integer")]
    #[OA\Property(property: "bathroomCount", type: "integer")]
    #[OA\Property(property: "additionalBathroomCount", type: "integer")]
    #[OA\Property(property: "floorCount", type: "integer")]
    #[OA\Property(property: "electricPower", type: "integer")]
    #[OA\Property(property: "viewCount", type: "integer")]
    #[OA\Property(property: "matchFilterCount", type: "integer")]
    #[OA\Property(property: "facing", ref: "#/components/schemas/FacingDirection")]
    #[OA\Property(property: "ownership", ref: "#/components/schemas/PropertyOwnership")]
    #[OA\Property(property: "verifyStatus", ref: "#/components/schemas/VerifyStatus")]
    #[OA\Property(property: "activeStatus", ref: "#/components/schemas/ActiveStatus")]
    #[OA\Property(property: "adminNote", ref: "#/components/schemas/AdminNote")]
    #[OA\Property(property: "cancellationNote", ref: "#/components/schemas/CancellationNote")]
    #[OA\Property(property: "cityName", type: "string")]
    #[OA\Property(property: "cityId", type: "integer")]
    #[OA\Property(property: "city", type: "string")]
    #[OA\Property(
        property: "pictureUrls",
        type: "array",
        items: new OA\Items(type: "string", format: "uri", example: "https://example.com/image.jpg")
    )]
    #[OA\Property(property: "coordinate", type: "object", properties: [
        new OA\Property(property: "latitude", type: "integer"),
        new OA\Property(property: "longitude", type: "integer")
    ])]
    #[OA\Property(property: "contact", type: "object", properties: [
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "company", type: "string")
    ])]
    #[OA\Property(property: "user", type: "object", properties: [
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "phoneNumber", type: "string"),
        new OA\Property(property: "profilePictureURL", type: "string"),
        new OA\Property(property: "city", type: "string"),
        new OA\Property(property: "cityId", type: "integer"),
        new OA\Property(property: "cityName", type: "string"),
        new OA\Property(property: "company", type: "string"),
        new OA\Property(property: "description", type: "string"),
    ])]
    #[OA\Property(property: "userCanEdit", type: "boolean")]
    #[OA\Property(property: "isPrivate", type: "boolean")]
    #[OA\Property(property: "withRewardAgreement", type: "boolean")]
    #[OA\Property(property: "isMultipleUnits", type: "boolean")]
    #[OA\Property(property: "closings", type: "array", items: new OA\Items(ref: "#/components/schemas/Closing"))]
    #[OA\Property(property: "updatedAt", type: "string", format: "date-time")]
    #[OA\Property(property: "createdAt", type: "string", format: "date-time")]
    #[OA\Property(property: "expiredAt", type: "string", format: "date-time")]
    #[OA\Property(property: "rawExpiredAt", type: "string", format: "date-time")]

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\Listing $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'listingId' => $prop->listingId,
            'listingIdStr' => (string)$prop->listingId,
            'sourceText' => $prop->sourceText,
            'title' => $prop->title,
            'propertyType' => $prop->propertyType,
            'listingType' => $prop->listingType,
            'listingForSale' => $prop->listingForSale ?? false,
            'listingForRent' => $prop->listingForRent ?? false,
            'address' => $prop->address,
            'description' => $prop->description,
            'price' => $prop->price ? (int) $prop->price : null,
            'rentPrice' => $prop->rentPrice ? (int) $prop->rentPrice : null,
            'lotSize' => $prop->lotSize ? (int) $prop->lotSize : null,
            'buildingSize' => $prop->buildingSize ? (int) $prop->buildingSize : null,
            'carCount' => $prop->carCount ? (int) $prop->carCount : null,
            'bedroomCount' => $prop->bedroomCount ? (int) $prop->bedroomCount : null,
            'additionalBedroomCount' => $prop->additionalBedroomCount ? (int) $prop->additionalBedroomCount : null,
            'bathroomCount' => $prop->bathroomCount ? (int) $prop->bathroomCount : null,
            'additionalBathroomCount' => $prop->additionalBathroomCount ? (int) $prop->additionalBathroomCount : null,
            'floorCount' => $prop->floorCount ? (int) $prop->floorCount : null,
            'electricPower' => $prop->electricPower ? (int) $prop->electricPower : null,
            'viewCount' => $prop->viewCount ?? 0,
            'matchFilterCount' => $prop->matchFilterCount ?? 0,
            'facing' => $prop->facing,
            'ownership' => $prop->ownership,
            'verifyStatus' => $prop->verifyStatus ?? '',
            'activeStatus' => $prop->activeStatus ?? '',
            'cityName' => $prop->cityName ?? '',
            'cityId' => $prop->cityId ?? 0,
            'city' => $prop->city ?? '',
            'pictureUrls' => $prop->pictureUrls ?? [],
            'coordinate' => [
                'latitude' => $prop->coordinate->latitude,
                'longitude' => $prop->coordinate->longitude,
            ],
            'contact' => [
                'name' => $prop->contact ? ($prop->contact['name'] ?? null) : null,
                'company' =>  $prop->contact ? ($prop->contact['company'] ?? null) : null,
            ],
            'user' => [
                'name' => $prop->user_profile?->name,
                'phoneNumber' => $prop->user_profile?->phoneNumber,
                'city' => $prop->user_profile?->city,
                'cityId' => $prop->user_profile?->cityId,
                'cityName' => $prop->user_profile?->cityName,
                'profilePictureURL' => $prop->user_profile?->picture
                    ? Photo::getGcsUrlFromFileName($prop->user_profile->picture)
                    : null,
                'company' => $prop->user_profile?->company,
                'description' => $prop->user_profile?->description,
            ],
            'userCanEdit' => $prop->user_can_edit ?? false,
            'isPrivate' => $prop->isPrivate ?? false,
            'withRewardAgreement' => $prop->withRewardAgreement ?? false,
            'isMultipleUnits' => $prop->isMultipleUnits ?? false,
            'adminNote' => $prop->adminNote ? AdminNoteResource::make($prop->adminNote)->resolve() : null,
            'cancellationNote' => $prop->cancellationNote
                ? CancellationNoteResource::make($prop->cancellationNote)->resolve()
                : null,
            'closings' => $prop->closings ? ClosingCollection::make($prop->closings)->resolve() : null,
            'updatedAt' => $prop->updated_at->isoFormat('D MMMM YYYY'),
            'createdAt' => $prop->updated_at->isoFormat('D MMMM YYYY'),
            'expiredAt' => isset($prop->expiredAt) && $prop->expiredAt instanceof Carbon
                ? $prop->expiredAt->isoFormat('D MMMM YYYY')
                : null,
            'rawExpiredAt' => isset($prop->expiredAt) && $prop->expiredAt instanceof Carbon
                ? $prop->expiredAt->format('Y-m-d H:i')
                : null,
        ];
    }
}
