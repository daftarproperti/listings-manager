<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ClosingCollection extends ResourceCollection
{
    public static $wrap = 'closings';
}
