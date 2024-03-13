<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TelegramUserCollection extends ResourceCollection
{
    public static $wrap = 'telegram_users';
}
