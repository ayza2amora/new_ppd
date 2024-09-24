<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is authenticated and has the correct role
        if (Auth::check() && Auth::user()->role === $role) {
            return $next($request);
        }

        // Redirect if user does not have the correct role
        return redirect('/')->with('error', 'You do not have access to this page.');
    }
}
