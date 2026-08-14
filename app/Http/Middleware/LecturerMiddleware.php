<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LecturerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = Auth::user()->role;
        if ($role !== 'lecturer' && $role !== 'admin') {
            abort(403, 'Unauthorized - Lecturer access required.');
        }

        return $next($request);
    }
}