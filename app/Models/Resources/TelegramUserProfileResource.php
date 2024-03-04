<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TelegramUserProfile",
 *     type="object",
 *)
 */
class TelegramUserProfileResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @OA\Property(property="id",type="integer",example="123")
     * @OA\Property(property="publicId",type="string",example="id-123")
     * @OA\Property(property="name",type="string",example="John Doe")
     * @OA\Property(property="phoneNumber",type="string",example="0811111")
     * @OA\Property(property="city",type="string",example="New York")
     * @OA\Property(property="description",type="string",example="I am a programmer")
     * @OA\Property(property="pricture",type="string",example="https://example.com/image.jpg")
     * @OA\Property(property="company",type="string",example="Google")
     * @OA\Property(property="isPublicProfile",type="bool", example=true)
     * @return array<mixed>
     */

    public function toArray($request)
    {
        /** @var \App\Models\TelegramUser $user */
        $user = $this->resource;
        $profile = $user->profile ? (object) $user->profile : null;
        return [
            'id' => $user->user_id,
            'publicId' => $user->_id,
            'name' => $profile?->name ?? trim($user->first_name.' '.$user->last_name),
            'city' => $profile?->city ?? null,
            'description' => $profile?->description ?? null,
            'company' => $profile?->company ?? null,
            'picture' => $profile?->picture ?? null,
            'phoneNumber' => $profile?->phoneNumber ?? null,
            'isPublicProfile' => $profile?->isPublicProfile ?? null,
        ];
    }
}
