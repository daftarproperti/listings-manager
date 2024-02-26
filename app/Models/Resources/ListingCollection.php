<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ListingCollection extends ResourceCollection
{
    public static $wrap = 'listings';
}
