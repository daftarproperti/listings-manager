<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\TokenValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DPApp
{
    use TokenValidation;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasHeader('Authorization')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $authHeader = $request->header('Authorization');
        if(!$authHeader) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = $this->validateToken($authHeader);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
