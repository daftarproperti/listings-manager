<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TelegramAllowlistGroupCollection extends ResourceCollection
{
    public static $wrap = 'telegram_allowlist_groups';
}
