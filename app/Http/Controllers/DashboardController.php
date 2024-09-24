<?php
namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Utilization;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get aggregated data for all provinces
        $allocations = Allocation::select('province', \DB::raw('SUM(fund_allocation) as amount'))
            ->groupBy('province')
            ->get();

        $utilizations = Utilization::select('province', \DB::raw('SUM(fund_utilized) as amount'))
            ->groupBy('province')
            ->get();

        // Aggregate the data for all provinces
        $totalAllocation = \DB::table('allocations')->sum('fund_allocation');
        $totalUtilization = \DB::table('utilizations')->sum('fund_utilized');
    
        // Get total target from allocations table
        $totalTarget = \DB::table('allocations')->sum('target');
    
        // Get total served (physical) from utilizations table
        $totalServed = \DB::table('utilizations')->sum('physical');
    
        return Inertia::render('Admin/Admin-dashboard', [
            'allocations' => $allocations,
            'utilizations' => $utilizations,
            'totalAllocation' => $totalAllocation,
            'totalUtilization' => $totalUtilization,
            'totalTarget' => $totalTarget,
            'totalServed' => $totalServed,
        ]);
    }
}
