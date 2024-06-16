<?php

namespace JaceApp\Jace\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Closure;
use Illuminate\Http\Request;

class ChannelVisibility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TODO: This is all temp code until we store it in a DB
        if (!Auth::check()) {
            $cacheKey = 'channel:1:visibility';
            $memberOnly = Redis::get($cacheKey) ?? 0;
            if ($memberOnly) {
                return response()->json(['message' => 'Members only chat'], 403);
            }

            return $next($request);
        }

        return $next($request);
    }
}
