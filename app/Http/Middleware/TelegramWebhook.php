<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhook
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
        $secretToken = $request->route('secret_token');
        if (!$secretToken || $secretToken !== config('services.telegram.webhook_access_secret')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
