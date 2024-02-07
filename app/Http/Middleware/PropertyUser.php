<?php

namespace App\Http\Middleware;

use App\Models\TelegramUser;
use Closure;
use Illuminate\Http\Request;

class PropertyUser
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
        /** @var \App\Models\Property $property */
        $property = $request->property;
        /** @var array<string, string> $propertyUser */
        $propertyUser = $property->user;

        if (!$propertyUser || ($propertyUser['userId'] !== app(TelegramUser::class)->user_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
