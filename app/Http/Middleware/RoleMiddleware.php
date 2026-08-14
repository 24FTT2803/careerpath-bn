<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if (Auth::user()->role === $role) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized - You do not have permission to access this page.');
    }
}