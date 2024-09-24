<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogController extends Controller
{
    public function index()
    {
        // Fetch all logs with specific fields and associated user details
        $logs = Log::with([
            'user:id,first_name,last_name',
            
        ])
        ->select('id', 'user_id', 'action', 'previous_value', 'new_value', 'created_at')
        ->get(); // Fetch all logs without filtering

        // Pass the logs to the admin logs Vue component
        return Inertia::render('Admin/Admin-logs', [
            'logs' => $logs,
        ]);
    }
}