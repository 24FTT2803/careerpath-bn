<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laravel's "verified" middleware, but only from a date.
 *
 * Switching verification on for everyone at once would lock out
 * every account that existed beforehand, none of which ever had
 * the chance to confirm. This asks only the accounts created
 * since the requirement began; the rest carry on and can
 * confirm from their settings whenever they like.
 */
class EnsureEmailIsVerifiedSince
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = Auth::user();

        if (! $user instanceof MustVerifyEmail) {
            return $next($request);
        }

        if ($user->hasVerifiedEmail()) {
            return $next($request);
        }

        if (! $user->mustVerifyEmailAddress()) {
            return $next($request);
        }

        /*
         * Anything reading JSON cannot follow a redirect to a
         * page, so it is told plainly instead.
         */
        if ($request->expectsJson()) {
            abort(
                403,
                'Confirm your email address to continue.'
            );
        }

        return redirect()->route('verification.notice');
    }
}
