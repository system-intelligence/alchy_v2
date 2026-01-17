<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->user() ? $request->user()->id : $request->ip();

        // Apply rate limiting based on route or user authentication
        if ($request->is('login') || $request->is('register')) {
            // Stricter limits for authentication routes: 10 requests per minute
            $executed = RateLimiter::attempt(
                'login:' . $key,
                10,
                function () use ($request, $next) {
                    return $next($request);
                },
                60 // 1 minute
            );

            if (!$executed) {
                return response('API rate limit exceeded.', 429);
            }

            return $executed;
        } else {
            // General rate limiting for authenticated users: 60 requests per minute
            $executed = RateLimiter::attempt(
                'api:' . $key,
                60,
                function () use ($request, $next) {
                    return $next($request);
                },
                60 // 1 minute
            );

            if (!$executed) {
                return response('API rate limit exceeded.', 429);
            }

            return $executed;
        }
    }
}