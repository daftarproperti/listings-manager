<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QueueWebhook
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
        $secretToken = $request->header('access-token');

        if (!$secretToken || $secretToken !== config('services.google.webhook_access_secret')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
