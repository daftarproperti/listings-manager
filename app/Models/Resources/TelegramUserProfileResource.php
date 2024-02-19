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
     * @OA\Property(property="name",type="string",example="John Doe")
     * @OA\Property(property="city",type="string",example="New York")
     * @OA\Property(property="description",type="string",example="I am a programmer")
     * @OA\Property(property="pricture",type="string",example="https://example.com/image.jpg")
     * @OA\Property(property="company",type="string",example="Google")
     * @return array<mixed>
     */

    public function toArray($request)
    {
        /** @var \App\Models\TelegramUser $user */
        $user = $this->resource;
        $profile = $user->profile ? (object) $user->profile : null;
        return [
            'id' => $user->user_id,
            'name' => $profile?->name ? $profile->name : trim($user->first_name.' '.$user->last_name),
            'city' => $profile?->city,
            'description' => $profile?->description,
            'company' => $profile?->company,
            'picture' => $profile?->picture
        ];
    }
}
