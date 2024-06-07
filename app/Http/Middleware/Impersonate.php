<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
        // The impersonation feature is only to help development, don't allow this in production.
        if (App::isProduction()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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

        App::singleton(User::class, static function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
