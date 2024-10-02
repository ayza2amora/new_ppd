<?php

namespace App\Http\Controllers;

use App\Models\ClientLog;
use App\Models\Province;
use App\Models\CityMuni;
use App\Models\Allocation; // Make sure the Allocation model is included
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CLogController extends Controller
{
    public function index()
    {
        // Get the currently authenticated user
        $user = Auth::user();

        // Fetch logs associated with the logged-in user
        $logs = ClientLog::with(['user', 'allocation', 'utilization'])
            ->where('user_id', $user->id) // Filter by the logged-in user's ID
            ->get()
            ->map(function ($log) {
                // Fetch city/municipality and province from utilization, then fall back to allocation
                if ($log->type === 'allocation') {
                    $provinceName = $this->getProvinceName($log->allocation->province);
                $cityMunicipalityName = $this->getCityMunicipalityName($log->allocation->city_municipality);
                $programName = $this->getProgramName($log->allocation->program); // Fetch program name from allocation
                } else {
                    $provinceName = $this->getProvinceName($log->utilization->province);
                    $cityMunicipalityName = $this->getCityMunicipalityName($log->utilization->city_municipality);
                    $programName = $this->getProgramName($log->utilization->program); // Fetch program name from utilization
                }

                // Logging the fetched data for debugging
                \Log::info('Log Province and City', [
                    'province_psgc' => $log->allocation->province ?? $log->utilization->province,
                    'city_municipality_psgc' => $log->allocation->city_municipality ?? $log->utilization->city_municipality,
                    'province_name' => $provinceName,
                    'city_municipality_name' => $cityMunicipalityName,
                ]);

                return [
                    'id' => $log->id,
                    'created_at' => $log->created_at,
                    'user' => [
                        'first_name' => $log->user->first_name ?? 'Unknown User',
                        'last_name' => $log->user->last_name ?? 'Unknown User',
                    ],
                    'type' => $log->type,
                    'action' => $log->action,
                    'record_id' => $log->record_id,
                    'province' => $provinceName,
                    'city_municipality' => $cityMunicipalityName,
                    'description' => $cityMunicipalityName . ', ' . $provinceName,
                    'program' => $programName, // Add program name to the logs
                ];
            });

        // Pass the filtered logs to the Inertia component
        return Inertia::render('Client/Client-logs', [
            'logs' => $logs,
        ]);
    }

    // Method for updating an allocation
    public function update(Request $request, $id)
    {
        // Find the allocation by ID
        $allocation = Allocation::findOrFail($id);

        // Log before updating the allocation
        \Log::info('Before Update Allocation', ['allocation' => $allocation]);

        // Update the allocation details including city_municipality and province
        $allocation->update([
            'city_municipality' => $request->city_municipality, // Make sure this is the PSGC code
            'province' => $request->province, // Make sure this is the PSGC code
            'fund' => $request->fund, // Update the fund value or other necessary fields
        ]);

        // Log after updating the allocation
        \Log::info('After Update Allocation', ['allocation' => $allocation]);

        // Log the action in the ClientLog
        ClientLog::create([
            'user_id' => Auth::id(),
            'action' => 'edited',
            'type' => 'allocation',
            'record_id' => $allocation->id,
            'description' => "Edited allocation for " . $this->getCityMunicipalityName($request->city_municipality) . ", " . $this->getProvinceName($request->province),
        ]);

        return back()->with('success', 'Allocation updated successfully.');
    }

    private function getProvinceName($provincePsgc)
    {
        if (!$provincePsgc) {
            return 'N/A';
        }

        // Fetch province by PSGC code
        $province = Province::where('psgc', $provincePsgc)->first();
        return $province ? $province->col_province : 'Unknown Province'; // Use 'col_province'
    }

    private function getCityMunicipalityName($cityMuniPsgc)
    {
        if (!$cityMuniPsgc) {
            return 'N/A';
        }

        // Fetch city/municipality by PSGC code
        $cityMuni = CityMuni::find($cityMuniPsgc);
        return $cityMuni ? $cityMuni->col_citymuni : 'Unknown City/Municipality'; // Adjust 'col_citymuni' to match the column in your table
    }

    private function getProgramName($programId)
{
    if (!$programId) {
        return 'N/A';
    }

    // Fetch the program name from the programs table by ID
    $program = \App\Models\Program::find($programId); // Assuming the Program model exists
    return $program ? $program->name : 'Unknown Program'; // Return the program name or 'Unknown Program'
}
}