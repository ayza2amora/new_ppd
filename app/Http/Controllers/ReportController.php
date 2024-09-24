<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Province;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        // Fetch all provinces along with their associated city municipalities, allocations, and utilizations
        $provinces = Province::with(['citymuni.allocations', 'citymuni.utilizations'])->get();

        // Group cities by province and district, including allocation and utilization data
        $groupedProvinces = $provinces->map(function ($province) {
            // Group cities by district within each province
            $groupedCities = $province->citymuni->groupBy('district');

            // Sum allocation and utilization for the province
            $totalProvinceAllocation = $province->citymuni->flatMap->allocations->sum('fund_allocation');
            $totalProvinceUtilization = $province->citymuni->flatMap->utilizations->sum('fund_utilized');
            $totalProvinceTarget = $province->citymuni->flatMap->allocations->sum('target'); // Assuming there's a `target` field
            $totalProvincePhysical = $province->citymuni->flatMap->utilizations->sum('physical'); // Assuming there's a `physical` field

            // Return province with grouped cities by district and include allocations/utilizations
            return [
                'psgc' => $province->psgc,
                'col_province' => $province->col_province,
                'total_allocation' => $totalProvinceAllocation,
                'total_utilization' => $totalProvinceUtilization,
                'total_target' => $totalProvinceTarget,
                'total_physical' => $totalProvincePhysical,
                'districts' => $groupedCities->mapWithKeys(function ($cities, $district) {
                    return [$district => [
                        'district' => $district,
                        'cities' => $cities->map(function ($city) {
                            // Sum allocations and utilizations for cities
                            $totalAllocation = $city->allocations->sum('fund_allocation');
                            $totalUtilization = $city->utilizations->sum('fund_utilized');
                            $totalTarget = $city->allocations->sum('target'); // Assuming there's a `target` field
                            $totalPhysical = $city->utilizations->sum('physical'); // Assuming there's a `physical` field

                            // Fetch all programs within the city
                            $programsByCity = DB::table('programs')
                                ->leftJoin('allocations', 'programs.id', '=', 'allocations.program')
                                ->leftJoin('utilizations', 'programs.id', '=', 'utilizations.program')
                                ->where('allocations.city_municipality', $city->psgc) // Fetch by `psgc`
                                ->select(
                                    'programs.id as program_id',
                                    'programs.name as program_name',
                                    'programs.logo as program_logo', // Fetch the logo
                                    DB::raw('COALESCE(SUM(allocations.target), 0) as total_target'),
                                    DB::raw('COALESCE(SUM(utilizations.physical), 0) as total_physical'),
                                    DB::raw('COALESCE(SUM(allocations.fund_allocation), 0) as total_allocation'),
                                    DB::raw('COALESCE(SUM(utilizations.fund_utilized), 0) as total_utilization')
                                )
                                ->groupBy('programs.id', 'programs.name', 'programs.logo') // Group by program
                                ->get()
                                ->map(function ($program) {
                                    return [
                                        'program_id' => $program->program_id,
                                        'program_name' => $program->program_name,
                                        'program_logo' => $program->program_logo, 
                                        'total_target' => $program->total_target,
                                        'total_physical' => $program->total_physical,
                                        'total_allocation' => $program->total_allocation,
                                        'total_utilization' => $program->total_utilization,
                                    ];
                                });

                            // Include program IDs in allocations and utilizations
                            $allocations = $city->allocations->map(function ($allocation) {
                                return [
                                    'program_id' => $allocation->program,
                                    'fund_allocation' => $allocation->fund_allocation,
                                    'target' => $allocation->target,
                                ];
                            });

                            $utilizations = $city->utilizations->map(function ($utilization) {
                                return [
                                    'program_id' => $utilization->program,
                                    'fund_utilized' => $utilization->fund_utilized,
                                    'physical' => $utilization->physical,
                                ];
                            });

                            return [
                                'psgc' => $city->psgc,
                                'col_citymuni' => $city->col_citymuni,
                                'total_allocation' => $totalAllocation,
                                'total_utilization' => $totalUtilization,
                                'total_target' => $totalTarget,
                                'total_physical' => $totalPhysical,
                                'allocations' => $allocations,
                                'utilizations' => $utilizations,
                                'programs' => $programsByCity, // Add programs grouped by city
                            ];
                        }),
                    ]];
                }),
            ];
        });

        // Fetch all programs across all provinces (for summary)
        $programs = DB::table('programs')
            ->leftJoin('allocations', 'programs.id', '=', 'allocations.program')
            ->leftJoin('utilizations', 'programs.id', '=', 'utilizations.program')
            ->select(
                'programs.id as program_id',
                'programs.name as program_name',
                'programs.logo as program_logo', // Fetch the logo
                DB::raw('COALESCE(SUM(allocations.target), 0) as total_target'),
                DB::raw('COALESCE(SUM(utilizations.physical), 0) as total_physical'),
                DB::raw('COALESCE(SUM(allocations.fund_allocation), 0) as total_allocation'),
                DB::raw('COALESCE(SUM(utilizations.fund_utilized), 0) as total_utilization')
            )
            ->groupBy('programs.id', 'programs.name', 'programs.logo') // Group by program ID
            ->get()
            ->map(function ($program) {
                return [
                    'program_id' => $program->program_id,
                    'program_name' => $program->program_name,
                    'program_logo' => $program->program_logo, 
                    'total_target' => $program->total_target,
                    'total_physical' => $program->total_physical,
                    'total_allocation' => $program->total_allocation,
                    'total_utilization' => $program->total_utilization,
                ];
            });

        // Render the view with grouped provinces and programs
        return Inertia::render('Admin/Admin-reports', [
            'provinces' => $groupedProvinces,
            'programs' => $programs, // Pass programs with associated data
            'isPreview' => true,
        ]);
    }
}
