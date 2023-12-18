<?php

namespace App\Http\Middleware;

use App\Helpers\TelegramInitDataValidator;
use Closure;
use Illuminate\Http\Request;

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

        if (!is_string($initData) || !TelegramInitDataValidator::isSafe(config('services.telegram.bot_token'), $initData)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
