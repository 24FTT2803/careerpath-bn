<?php

namespace App\Http\Middleware;

use App\Services\Business\PremiumExpiry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends "your Premium has ended" notifications without needing a
 * scheduled task: page loads check for expired grants, at most
 * once a minute. A failure here must never break the page.
 */
class CheckPremiumExpiry
{
    public function __construct(
        private PremiumExpiry $expiry
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->expiry->runIfDue();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $next($request);
    }
}
