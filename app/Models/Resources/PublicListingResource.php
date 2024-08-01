<?php

namespace App\Models\Resources;

use App\Helpers\Ecies;
use App\Helpers\TelegramPhoto;
use App\Models\Enums\VerifyStatus;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PublicListing",
    type: "object"
)]
/**
 * Representation of a Listing which is available publicly.
 */
class PublicListingResource extends JsonResource
{
    #[OA\Property(property: "listingId", type: "integer")]
    #[OA\Property(property: "listingIdStr", type: "string")]
    #[OA\Property(property: "title", type: "string")]
    #[OA\Property(property: "propertyType", ref: "#/components/schemas/PropertyType")]
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
    #[OA\Property(property: "bathroomCount", type: "integer")]
    #[OA\Property(property: "floorCount", type: "integer")]
    #[OA\Property(property: "electricPower", type: "integer")]
    #[OA\Property(property: "facing", ref: "#/components/schemas/FacingDirection")]
    #[OA\Property(property: "ownership", ref: "#/components/schemas/PropertyOwnership")]
    #[OA\Property(property: "isVerified", type: "boolean")]
    #[OA\Property(property: "cityName", type: "string")]
    #[OA\Property(property: "cityId", type: "integer")]
    #[OA\Property(property: "pictureUrls", type: "array", items: new OA\Items(type: "string", format: "uri", example: "https://example.com/image.jpg"))]
    #[OA\Property(property: "coordinate", type: "object", properties: [
        new OA\Property(property: "latitude", type: "integer"),
        new OA\Property(property: "longitude", type: "integer")
    ])]
    #[OA\Property(property: "updatedAt", type: "string", format: "date-time")]

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\Listing $listing */
        $listing = $this->resource;

        $ethPrivateHex = env('ETH_PRIVATE_KEY');
        $pubHex = is_string($ethPrivateHex) ? substr(Ecies::publicHexFromPrivateHex($ethPrivateHex), 2) : null;

        return [
            'listingId' => $listing->listingId,
            'listingIdStr' => (string)$listing->listingId,
            'title' => $listing->title,
            'propertyType' => $listing->propertyType,
            'listingForSale' => $listing->listingForSale ?? false,
            'listingForRent' => $listing->listingForRent ?? false,
            'address' => $listing->address,
            'description' => $listing->description,
            'price' => $listing->price,
            'rentPrice' => $listing->rentPrice,
            'lotSize' => $listing->lotSize,
            'buildingSize' => $listing->buildingSize,
            'carCount' => $listing->carCount,
            'bedroomCount' => $listing->bedroomCount,
            'bathroomCount' => $listing->bathroomCount,
            'floorCount' => $listing->floorCount,
            'electricPower' => $listing->electricPower,
            'facing' => $listing->facing,
            'ownership' => $listing->ownership,
            'isVerified' => $listing->verifyStatus === VerifyStatus::APPROVED,
            'cityName' => $listing->cityName,
            'cityId' => $listing->cityId,
            'pictureUrls' => $listing->pictureUrls,
            'coordinate' => [
                'latitude' => $listing->coordinate->latitude,
                'longitude' => $listing->coordinate->longitude,
            ],
            'withRewardAgreement' => $listing->withRewardAgreement,
            'isMultipleUnits' => $listing->isMultipleUnits,
            'updatedAt' => $listing->updated_at->toIso8601String(),
            'registrant' => [
                'name' => $listing->user_profile?->name,
                // Only DP can decrypt this since only DP has the private key of $pubHex
                'phoneNumberEncrypted' => $pubHex && $listing->user_profile?->phoneNumber ?
                    Ecies::encrypt($pubHex, $listing->user_profile->phoneNumber) :
                    null,
                // The hash of userid:phone.
                //
                // User ID is added so that it's difficult to brute force which phone number results to this hash.
                // It is also difficult to check whether a particular phone number results to this hash.
                //
                // This still allows listing registrant to detect their own listings since they know their user id +
                // phone number and can check whether userid:phone equals this hash. This is a feature by design.
                'phoneNumberHash' => $listing->user_profile?->phoneNumber ?
                    hash(
                        'sha256',
                        User::generateUserId($listing->user_profile->phoneNumber) . ':' .
                            $listing->user_profile->phoneNumber
                    ) :
                    null,
                'profilePictureURL' => $listing->user_profile?->picture ?
                    TelegramPhoto::getGcsUrlFromFileName($listing->user_profile->picture) :
                    null,
                'company' => $listing->user_profile?->company,
            ],
        ];
    }
}
