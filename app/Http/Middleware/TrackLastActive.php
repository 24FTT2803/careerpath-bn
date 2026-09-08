<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    /**
     * Stamp the authenticated user's last_active_at timestamp.
     *
     * Throttled to once per minute per user so this doesn't add an
     * extra write query to every single request - "online" only needs
     * minute-level precision for a badge, not per-request accuracy.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (
                ! $user->last_active_at
                || $user->last_active_at->lt(now()->subMinute())
            ) {
                $user->forceFill(['last_active_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}