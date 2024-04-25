<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SavedSearchCollection extends ResourceCollection
{
    public static $wrap = 'saved_searches';
}
