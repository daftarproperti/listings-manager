<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Sanctum\PersonalAccessToken;
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

        $abilities = $accessToken->abilities;
        $impersonatedBy = $this->extractAbility($abilities, 'impersonated_by');

        $user = $accessToken->tokenable;
        if ($impersonatedBy) {
            $user->setImpersonatedBy($impersonatedBy);
        }

        return $user;
    }

    /**
     * Helper function to extract a specific ability from token abilities.
     *
     * @param array<int, string>|null $abilities
     * @param string $prefix
     * @return string|null
     */
    private function extractAbility(?array $abilities, string $prefix): ?string
    {
        if (is_null($abilities)) {
            return null;
        }

        foreach ($abilities as $ability) {
            if (str_starts_with($ability, $prefix . ':')) {
                return explode(':', $ability, 2)[1];
            }
        }
        return null;
    }
}
