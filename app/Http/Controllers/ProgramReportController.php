<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProgramReportController extends Controller
{
    public function index()
    {
        // Fetch all programs along with their allocations and utilizations
        $programs = Program::with(['allocations', 'utilizations'])->get();

        // Group programs by allocations and utilizations, including associated province and city data
        $groupedPrograms = $programs->map(function ($program) {
            // Get allocations and utilizations for the program, grouped by city
            $allocationsByCity = $program->allocations->groupBy('city_municipality')->map(function ($allocations, $city) {
                return [
                    'city_municipality' => $city,
                    'total_allocation' => $allocations->sum('fund_allocation'),
                    'total_target' => $allocations->sum('target'), // Assuming there's a `target` field
                ];
            });

            $utilizationsByCity = $program->utilizations->groupBy('city_municipality')->map(function ($utilizations, $city) {
                return [
                    'city_municipality' => $city,
                    'total_utilized' => $utilizations->sum('fund_utilized'),
                    'total_physical' => $utilizations->sum('physical'), // Assuming there's a `physical` field
                ];
            });

            // Combine the allocations and utilizations by city
            $cities = $allocationsByCity->map(function ($allocationData) use ($utilizationsByCity) {
                $cityUtilization = $utilizationsByCity->get($allocationData['city_municipality'], [
                    'total_utilized' => 0,
                    'total_physical' => 0,
                ]);
                
                return [
                    'city_municipality' => $allocationData['city_municipality'],
                    'total_allocation' => $allocationData['total_allocation'],
                    'total_target' => $allocationData['total_target'],
                    'total_utilized' => $cityUtilization['total_utilized'],
                    'total_physical' => $cityUtilization['total_physical'],
                ];
            });

            // Aggregate totals for the program across all cities
            $totalProgramAllocation = $cities->sum('total_allocation');
            $totalProgramUtilization = $cities->sum('total_utilized');
            $totalProgramTarget = $cities->sum('total_target');
            $totalProgramPhysical = $cities->sum('total_physical');

            return [
                'program_id' => $program->id,
                'program_name' => $program->name,
                'program_logo' => $program->logo,
                'total_allocation' => $totalProgramAllocation,
                'total_utilization' => $totalProgramUtilization,
                'total_target' => $totalProgramTarget,
                'total_physical' => $totalProgramPhysical,
                'cities' => $cities,
            ];
        });

        // Render the view with grouped programs
        return Inertia::render('Admin/ProgramReports', [
            'programs' => $groupedPrograms,
            'isPreview' => true,
        ]);
    }
}
