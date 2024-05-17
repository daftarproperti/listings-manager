<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserProfile",
 *     type="object",
 *)
 */
class UserProfileResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Property(property="name",type="string",example="John Doe")
     * @OA\Property(property="description",type="string",example="I am a programmer")
     * @OA\Property(property="city",type="string",example="New York")
     * @OA\Property(property="picture",type="string",example="https://example.com/image.jpg")
     * @OA\Property(property="company",type="string",example="Google")
     * @OA\Property(property="cityOfOperation",type="string",example="Jakarta")
     * @OA\Property(property="isPublicProfile",type="bool", example=true)
     * @return array<mixed>
     */

    public function toArray($request)
    {
        /** @var \App\Models\User $user */
        $user = $this->resource;
        $profile = $user->profile;
        return [
            'name' => $profile?->name ?? trim($user->firstName . ' ' . $user->lastName),
            'city' => $profile?->city,
            'description' => $profile?->description,
            'company' => $profile?->company,
            'cityOfOperation' => $profile?->cityOfOperation,
            'picture' => $profile?->picture,
            'isPublicProfile' => $profile?->isPublicProfile,
        ];
    }
}
