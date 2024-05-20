<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *)
 */
class UserResource extends JsonResource
{
    public static $wrap = null;
    /**
     * @OA\Property(property="id",type="string")
     * @OA\Property(property="username",type="string")
     * @OA\Property(property="phoneNumber",type="string")
     * @OA\Property(property="accountType",ref="#/components/schemas/AccountType")
     * @OA\Property(property="email",type="string")
     * @OA\Property(property="name",type="string")
     * @OA\Property(property="city",type="string")
     * @OA\Property(property="description",type="string")
     * @OA\Property(property="picture",type="string")
     * @OA\Property(property="company",type="string")
     * @OA\Property(property="isPublicProfile",type="bool")
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\User $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'username' => $prop->username,
            'phoneNumber' => $prop->phoneNumber,
            'accountType' => $prop->accountType,
            'email' => $prop->email,
            'password' => $prop->password,
            'name' => $prop->name,
            'city' => $prop->city,
            'description' => $prop->description,
            'company' => $prop->company,
            'picture' => $prop->picture,
            'isPublicProfile' => $prop->isPublicProfile,

            // Deprecated
            'profile' => [
                'name' => $prop->name,
                'city' => $prop->city,
                'description' => $prop->description,
                'pictureURL' => $prop->picture,
                'isPublicProfile' => $prop->isPublicProfile,
                'company' => $prop->company,
            ]
        ];
    }
}
