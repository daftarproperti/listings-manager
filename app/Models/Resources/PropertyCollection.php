<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyCollection extends ResourceCollection
{
    public static $wrap = 'properties';
}
