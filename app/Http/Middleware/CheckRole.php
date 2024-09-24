<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {   
       \Log::info('Role middleware triggered with role: ' . $role);
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect('/login');
        }
        // Get the current authenticated user
        $user = Auth::user();

        // Check if the user's role matches the required role
        if ($role !== null && $user->role !== $role) {
            abort(403, 'Unauthorized action.');
        }

        // Allow the request to proceed
        return $next($request);
    }
}
