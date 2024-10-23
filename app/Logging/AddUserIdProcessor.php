<?php

namespace App\Logging;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Monolog\LogRecord;

/**
 * Adds DP User ID to log entries, if authenticated.
 * This helps debugging when user reports a problem.
 * To be used as a Monolog processor.
 */
class AddUserIdProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $user = Auth::user();
        if ($user instanceof User) {
            return $record->with(context: array_merge($record->context, [
                'dp_user_id' => (string)$user->user_id,
            ]));
        }
        return $record;
    }
}
