<?php
namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Utilization;
use App\Models\Province;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
         // Fetch year and quarter from request, default to current year and quarter if not provided
    $selectedYear = $request->input('year', date('Y'));
    $selectedQuarter = $request->input('quarter', ceil(date('n') / 3));

   // Get unfiltered data for the bar graph (ReportsChart)
$allAllocations = Allocation::select('province', 'fund_allocation', \DB::raw('YEAR(created_at) as year'), \DB::raw('QUARTER(created_at) as quarter'))
->get();

$allUtilizations = Utilization::select('province', 'fund_utilized', \DB::raw('YEAR(created_at) as year'), \DB::raw('QUARTER(created_at) as quarter'))
->get();

// Filter allocations and utilizations by selected year and quarter
$filteredAllocations = $allAllocations->filter(function ($allocation) use ($selectedYear, $selectedQuarter) {
return $allocation->year == $selectedYear && $allocation->quarter == $selectedQuarter;
});

$filteredUtilizations = $allUtilizations->filter(function ($utilization) use ($selectedYear, $selectedQuarter) {
return $utilization->year == $selectedYear && $utilization->quarter == $selectedQuarter;
});

// Format the data for the chart, grouping by province
$formattedAllocations = $filteredAllocations->groupBy('province')->map(function ($items, $province) {
// Get the province name (col_province) from the tbl_province table based on the psgc
$provinceName = DB::table('tbl_province')->where('psgc', $province)->value('col_province');

return [
    'province' => $provinceName ?? 'Unknown Province', // Safely access col_province
    'amount' => $items->sum('fund_allocation'),
    'year' => $items->first()->year,
    'quarter' => $items->first()->quarter,
];
})->values();

$formattedUtilizations = $filteredUtilizations->groupBy('province')->map(function ($items, $province) {
// Get the province name (col_province) from the tbl_province table based on the psgc
$provinceName = DB::table('tbl_province')->where('psgc', $province)->value('col_province');

return [
    'province' => $provinceName ?? 'Unknown Province', // Safely access col_province
    'amount' => $items->sum('fund_utilized'),
    'year' => $items->first()->year,
    'quarter' => $items->first()->quarter,
];
})->values();

        // Filter allocations and utilizations by year and quarter for other data
        $filteredAllocations = $allAllocations
            ->where('year', $selectedYear)
            ->where('quarter', $selectedQuarter);

        $filteredUtilizations = $allUtilizations
            ->where('year', $selectedYear)
            ->where('quarter', $selectedQuarter);

       // Data for programs with allocations and utilizations
        $programAllocations = Allocation::select('programs.name as program', \DB::raw('SUM(fund_allocation) as total_allocation'))
            ->join('programs', 'allocations.program', '=', 'programs.id')
            ->whereYear('allocations.created_at', $selectedYear)
            ->whereRaw('QUARTER(allocations.created_at) = ?', [$selectedQuarter])
            ->groupBy('programs.name')
            ->get();

        $programUtilizations = Utilization::select('programs.name as program', \DB::raw('SUM(fund_utilized) as total_utilization'))
            ->join('programs', 'utilizations.program', '=', 'programs.id')
            ->whereYear('utilizations.created_at', $selectedYear)
            ->whereRaw('QUARTER(utilizations.created_at) = ?', [$selectedQuarter])
            ->groupBy('programs.name')
            ->get();

        // Merge program allocations and utilizations
        $programData = $programAllocations->map(function ($allocation) use ($programUtilizations) {
            $utilization = $programUtilizations->firstWhere('program', $allocation->program);
            return [
                'program' => $allocation->program,
                'total_allocation' => $allocation->total_allocation,
                'total_utilization' => $utilization ? $utilization->total_utilization : 0,
            ];
        });

        // Add programs that have utilizations but no allocations
        $programUtilizations->each(function ($utilization) use (&$programData) {
            if (!$programData->contains('program', $utilization->program)) {
                $programData->push([
                    'program' => $utilization->program,
                    'total_allocation' => 0,
                    'total_utilization' => $utilization->total_utilization,
                ]);
            }
        });


       // Merge allocations and utilizations by province
$provinceData = $filteredAllocations->groupBy('province')->map(function ($allocation) use ($filteredUtilizations) {
    $psgc = $allocation->first()->province;

    // Get the province name (col_province) from the tbl_province table based on the psgc
    $provinceName = DB::table('tbl_province')->where('psgc', $psgc)->value('col_province');

    $total_allocation = $allocation->sum('fund_allocation');
    $total_utilization = $filteredUtilizations->where('province', $psgc)->sum('fund_utilized');

    return [
        'province' => $provinceName ?? 'Unknown Province', // Fallback if province not found
        'total_allocation' => $total_allocation,
        'total_utilization' => $total_utilization ?? 0,
    ];
})->values();

// Aggregate totals for the selected year and quarter
$totalAllocation = $filteredAllocations->sum('fund_allocation');
$totalUtilization = $filteredUtilizations->sum('fund_utilized');
$totalTarget = Allocation::whereYear('created_at', $selectedYear)
    ->whereRaw('QUARTER(created_at) = ?', [$selectedQuarter])
    ->sum('target');
$totalServed = Utilization::whereYear('created_at', $selectedYear)
    ->whereRaw('QUARTER(created_at) = ?', [$selectedQuarter])
    ->sum('physical');

// Render the dashboard view with all the necessary data
return Inertia::render('Admin/Admin-dashboard', [
    'provinceData' => $provinceData,
    'programData' => $programData,
    'allocations' => $formattedAllocations,
    'utilizations' => $formattedUtilizations,
    'totalAllocation' => $totalAllocation,
    'totalUtilization' => $totalUtilization,
    'totalTarget' => $totalTarget,
    'totalServed' => $totalServed,
    'selectedYear' => $selectedYear,
    'selectedQuarter' => $selectedQuarter,
]);

    }
}