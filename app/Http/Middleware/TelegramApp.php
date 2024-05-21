<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Helpers\TelegramInitDataValidator;
use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use App\Models\Sanctum\PersonalAccessToken;

class TelegramApp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader('Authorization')) {
            $authHeader = $request->header('Authorization');
            if (!$authHeader) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if (strpos($authHeader, 'Bearer ') != 0) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $token = substr($authHeader, 7);

            $accessToken = PersonalAccessToken::findToken($token);
            if (!$accessToken) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            /** @var string $expiresAtString */
            $expiresAtString = $accessToken->expires_at;
            if (!$expiresAtString) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $now = Carbon::now()->timestamp;
            $tokenExpiryTimestamp = Carbon::parse($expiresAtString)->timestamp;
            if ($tokenExpiryTimestamp < $now) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $user = $accessToken->tokenable;
            App::singleton(User::class, static function () use ($user) {
                return $user;
            });

            return $next($request);
        }

        $initData = $request->header('x-init-data');

        if (
            !is_string($initData) ||
            !TelegramInitDataValidator::isSafe(type(config('services.telegram.bot_token'))->asString(), $initData)
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $telegramUser = $this->telegramUserAuth($initData);

        if (!$telegramUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        App::singleton(TelegramUser::class, static function () use ($telegramUser) {
            return $telegramUser;
        });

        return $next($request);
    }

    private function telegramUserAuth(string $initData): ?TelegramUser
    {
        parse_str(rawurldecode($initData), $initDataArray);

        $user = [];

        if (isset($initDataArray['user'])) {
            assert(is_string($initDataArray['user']));
            /** @var array<string> $user */
            $user = json_decode($initDataArray['user'], true);
        } else {
            /**
             * if auth is from telegram login widget there are no `user` field in initData
             * so we need to parse it from `id`, `first_name`, `last_name`, `username`
             * https://core.telegram.org/widgets/login
             **/
            $user = Arr::only($initDataArray, ['id', 'first_name', 'last_name', 'username']);
        }

        if (empty($user)) {
            return null;
        }

        /** @var TelegramUser|null $telegramUser */
        $telegramUser = TelegramUser::where('user_id', (int)$user['id'])
            ->firstOrCreate([
                'user_id' => (int)$user['id'],
            ], [
                'first_name' => $user['first_name'],
                'last_name' => Arr::get($user, 'last_name'),
                'username' => Arr::get($user, 'username'),
            ]);

        return $telegramUser;
    }
}
