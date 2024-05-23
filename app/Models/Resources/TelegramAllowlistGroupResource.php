<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TelegramAllowlistGroup",
    type: "object"
)]
class TelegramAllowlistGroupResource extends JsonResource
{
    #[OA\Property(property: "id", type: "string")]
    #[OA\Property(property: "chatId", type: "integer")]
    #[OA\Property(property: "groupName", type: "string")]
    #[OA\Property(property: "sampleMessage", type: "string")]
    #[OA\Property(property: "allowed", type: "boolean")]
    #[OA\Property(property: "createdAt", type: "string")]

    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\TelegramAllowlistGroup $allowlist */
        $allowlist = $this->resource;

        return [
            'id' => $allowlist->id,
            'chatId' => $allowlist->chatId,
            'groupName' => $allowlist->groupName,
            'sampleMessage' => $allowlist->sampleMessage,
            'allowed' => $allowlist->allowed,
            'createdAt' => $allowlist->created_at?->format('d F Y H:i'),
        ];
    }
}
