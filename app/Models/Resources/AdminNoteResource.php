<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminNoteResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\AdminNote $prop */
        $prop = $this->resource;

        return [
            'email' => $prop->email,
            'message' => $prop->message,
            'date' => $prop->date->toIso8601ZuluString(),
        ];
    }
}
