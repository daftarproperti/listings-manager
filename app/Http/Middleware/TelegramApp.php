<?php

namespace App\Http\Middleware;

use App\Helpers\Assert;
use App\Helpers\TelegramInitDataValidator;
use App\Models\TelegramUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

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
        $initData = $request->header('x-init-data');

        if (
            !is_string($initData) ||
            !TelegramInitDataValidator::isSafe(Assert::string(config('services.telegram.bot_token')), $initData)
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

    private function telegramUserAuth(string $initData) : ?TelegramUser
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

        $telegramUser = TelegramUser::where('user_id', $user['id'])
            ->firstOrCreate([
                'user_id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => Arr::get($user, 'last_name'),
                'username' => Arr::get($user, 'username'),
            ]);

        return $telegramUser;
    }
}
