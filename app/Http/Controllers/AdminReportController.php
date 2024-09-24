<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Allocation;
use App\Models\Utilization;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Program;

class AdminReportController extends Controller
{
   
    public function program()
    {
        // Fetch programs from the database
        $programs = Program::all();

        // Pass data to the Inertia component
        return Inertia::render('Admin/Admin-reports', [
            'programs' => $programs
        ]);
    }
}
