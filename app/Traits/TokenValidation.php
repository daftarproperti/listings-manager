<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\App;
use App\Models\User;

trait TokenValidation
{
    /**
     * Validate the token from the request header.
     *
     * @param  string  $authHeader
     * @return \App\Models\User|null
     */
    public function validateToken(string $authHeader): ?User
    {
        if (!$authHeader) {
            return null;
        }

        if (strpos($authHeader, 'Bearer ') !== 0) {
            return null;
        }

        $token = substr($authHeader, 7);

        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            return null;
        }

        /** @var string $expiresAtString */
        $expiresAtString = $accessToken->expires_at;
        if (!$expiresAtString) {
            return null;
        }

        $now = Carbon::now()->timestamp;
        $tokenExpiryTimestamp = Carbon::parse($expiresAtString)->timestamp;
        if ($tokenExpiryTimestamp < $now) {
            return null;
        }

        return $accessToken->tokenable;
    }
}
