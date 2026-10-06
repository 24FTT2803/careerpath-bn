<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'admin') {
            /*
             * The wording reaches the person on the 403 page, so
             * it says which part of the site this was rather than
             * only that something was refused.
             */
            abort(
                403,
                'This page is part of the administrator tools, '
                .'which your account does not include.'
            );
        }

        return $next($request);
    }
}
