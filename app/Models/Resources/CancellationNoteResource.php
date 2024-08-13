<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CancellationNoteResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<mixed>
     */
    public function toArray($request)
    {
        /** @var \App\Models\CancellationNote $prop */
        $prop = $this->resource;

        return [
            'reason' => $prop->reason,
            'status' => $prop->status,
        ];
    }
}
