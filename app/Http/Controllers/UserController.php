<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    // Universal method to redirect based on role
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin-dashboard');
        } elseif ($user->role === 'user') {
            return redirect()->route('client-reports');
        }

        return abort(403, 'Unauthorized access.');
    }
}


