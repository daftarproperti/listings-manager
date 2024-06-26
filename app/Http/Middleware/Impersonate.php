<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\TokenValidation;

class Impersonate
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
        // TODO: Implement stricter mechanism for production environment impersonation.
        // It is fine to relax this for now since there is no sensitive transaction yet.

        $rootUsers = type(config('services.root_users'))->asArray();

        $authHeader = $request->header('Authorization');
        if(!$authHeader) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = $this->validateToken($authHeader);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!in_array($user->phoneNumber, $rootUsers)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
