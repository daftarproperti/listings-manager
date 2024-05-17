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
     * @OA\Property(property="firstName",type="string")
     * @OA\Property(property="lastName",type="string")
     * @OA\Property(property="username",type="string")
     * @OA\Property(property="phoneNumber",type="string")
     * @OA\Property(property="accountType",ref="#/components/schemas/AccountType")
     * @OA\Property(property="email",type="string")
     * @OA\Property(property="password",type="string")
     * @OA\Property(property="profile",ref="#/components/schemas/UserProfile")
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\User $prop */
        $prop = $this->resource;

        return [
            'id' => $prop->id,
            'firstName' => $prop->firstName,
            'lastName' => $prop->lastName,
            'username' => $prop->username,
            'phoneNumber' => $prop->phoneNumber,
            'accountType' => $prop->accountType,
            'email' => $prop->email,
            'password' => $prop->password,
            'profile' => [
                'name' => $prop->profile?->name,
                'city' => $prop->profile?->city,
                'description' => $prop->profile?->description,
                'pictureURL' => $prop->profile?->picture,
                'isPublicProfile' => $prop->profile?->isPublicProfile,
                'company' => $prop->profile?->company,
                'cityOfOperation' => $prop->profile?->cityOfOperation,
            ],
        ];
    }
}
