<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class ThrottleOTPRequest
{
    protected RateLimiter $limiter;
    protected int $decayMinutes = 1;
    protected int $maxAttempts = 3;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $throttleKey
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $throttleKey)
    {
        $key = 'throttle_' . $request->input($throttleKey);
        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts)) {
            return response()->json(['error' => 'Too many attempts. Please try again later.'], 429);
        }
        $this->limiter->hit($key, $this->decayMinutes * 60);

        return $next($request);
    }
}
