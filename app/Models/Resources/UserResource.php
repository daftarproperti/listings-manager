<?php

namespace App\Models\Resources;

use App\Helpers\Photo;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    type: 'object'
)]
class UserResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'string')]
    #[OA\Property(property: 'userId', type: 'integer')]
    #[OA\Property(property: 'userIdStr', type: 'string')]
    #[OA\Property(property: 'publicId', type: 'string')]
    #[OA\Property(property: 'username', type: 'string')]
    #[OA\Property(property: 'phoneNumber', type: 'string')]
    #[OA\Property(property: 'accountType', ref: '#/components/schemas/AccountType')]
    #[OA\Property(property: 'email', type: 'string')]
    #[OA\Property(property: 'name', type: 'string')]
    #[OA\Property(property: 'city', type: 'string')]
    #[OA\Property(property: 'cityId', type: 'integer')]
    #[OA\Property(property: 'cityName', type: 'string')]
    #[OA\Property(property: 'description', type: 'string')]
    #[OA\Property(property: 'picture', type: 'string')]
    #[OA\Property(property: 'company', type: 'string')]
    #[OA\Property(property: 'isPublicProfile', type: 'bool')]
    #[OA\Property(property: 'secretKey', type: 'string')]

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\User $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'userId' => $prop->user_id,
            'userIdStr' => (string)$prop->user_id,
            'publicId' => $prop->_id,
            'username' => $prop->username,
            'phoneNumber' => $prop->phoneNumber,
            'accountType' => $prop->accountType,
            'email' => $prop->email,
            'password' => $prop->password,
            'name' => $prop->name,
            'city' => $prop->city,
            'cityId' => $prop->cityId,
            'cityName' => $prop->cityName,
            'description' => $prop->description,
            'company' => $prop->company,
            'picture' => $prop->picture ? Photo::getGcsUrlFromFileName($prop->picture) : null,
            'isPublicProfile' => $prop->isPublicProfile,
            'secretKey' => $prop->secretKey,

            // Deprecated
            'profile' => [
                'name' => $prop->name,
                'publicId' => $prop->_id,
                'city' => $prop->city,
                'description' => $prop->description,
                'picture' => $prop->picture ? Photo::getGcsUrlFromFileName($prop->picture) : null,
                'isPublicProfile' => $prop->isPublicProfile,
                'company' => $prop->company,
            ]
        ];
    }
}
