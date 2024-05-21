<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\TelegramUser;

class DPAuth
{
    public static function getUser(): TelegramUser|User
    {
        /** @var User */
        $user = app(User::class);
        if (isset($user->user_id) && $user->user_id) return $user;

        return app(TelegramUser::class);
    }
}
