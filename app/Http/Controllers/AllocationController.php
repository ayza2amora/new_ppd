<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Allocation;
use Inertia\Inertia;
use App\Models\Province;
use App\Models\CityMuni;
use App\Models\Program;
use App\Models\ClientLog; // Import ClientLog model
use Illuminate\Support\Facades\Auth;
class AllocationController extends Controller
{
    public function index()
    {
        $allocations = Allocation::with(['province', 'citymuni', 'program'])->get();

        \Log::info('Allocations Data:', $allocations->toArray());

        return response()->json([
            'allocations' => $allocations
        ]);
    }
    public function create()
    {
        $provinces = Province::all();
        $programs = Program::all();
    
        return Inertia::render('Client/AllocationForm', [
            'provinces' => $provinces,
           'programs' => $programs->map(function ($program) {
                return [
                    'id' => $program->id,
                    'name' => $program->name,
                    'status' => $program->status,
                ];
            }),
            'cities' => [], 
            
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Request data:', $request->all());
    
        // Fetch the program by name and check if it's restricted
        $program = Program::where('name', $request->program)->first();
    
        if (!$program) {
            return back()->withErrors(['program' => 'Invalid program selected.'])->withInput();
        }
    
        if ($program->status == 1) { // Assuming 1 means "restricted"
            return back()->withErrors(['program' => 'You cannot submit an allocation for a restricted program.'])->withInput();
        }
    
        // Fetch the PSGC codes for province and city_municipality based on names
        $province = Province::where('col_province', $request->province)->first();
        $city_municipality = CityMuni::where('col_citymuni', $request->city_municipality)->first();
    
        if (!$province || !$city_municipality) {
            return back()->withErrors(['location' => 'Invalid province or city/municipality selected.'])->withInput();
        }
    
        // Log the PSGC codes for debugging purposes
        \Log::info('Province PSGC:', ['psgc' => $province->psgc]);
        \Log::info('City Municipality PSGC:', ['psgc' => $city_municipality->psgc]);
    
        // Validate the incoming request
        $validatedData = $request->validate([
            'province' => 'required|string|max:255', // Validate province name for input
            'city_municipality' => 'required|string|max:255', // Validate city name for input
            'program' => 'required|string|max:100',
            'target' => 'required|integer|min:0',
            'fund_allocation' => 'required|integer|min:0',
        ]);
    
        // Override the province and city names with the PSGC codes
        $validatedData['province'] = $province->psgc;
        $validatedData['city_municipality'] = $city_municipality->psgc;
        $validatedData['program'] = $program->id; // Store program ID
    
        // Create a new allocation record
        $allocation = Allocation::create($validatedData);
    
        \Log::info('Validated data:', $validatedData);
    
        // Log the action of creating a new allocation
        ClientLog::create([
            'user_id' => auth()->id(), // Assuming you're using Laravel's built-in auth
            'action' => 'added',
            'type' => 'allocation',
            'record_id' => $allocation->id, // The new allocation's ID
        ]);
    
        // Redirect to client reports page with a success message
        return redirect()->route('client-reports')->with('success', 'Allocation created successfully');
    }
    
public function update(Request $request, Allocation $allocation)
{
    \Log::info('Update Request received:', $request->all());

    // Fetch the program by name and check if it's restricted
    $program = Program::where('name', $request->program)->first();

    if (!$program) {
        \Log::error('Program not found:', ['program' => $request->program]);
        return back()->withErrors(['program' => 'Invalid program selected.'])->withInput();
    }

    if ($program->status == 1) {
        \Log::error('Program is restricted:', ['program' => $program->name]);
        return back()->withErrors(['program' => 'You cannot update an allocation for a restricted program.'])->withInput();
    }

    // Fetch the PSGC codes for province and city_municipality 
    $province = Province::where('col_province', $request->province)->first();
    $city_municipality = CityMuni::where('col_citymuni', $request->city_municipality)->first();

    if (!$province || !$city_municipality) {
        \Log::error('Invalid location selection:', [
            'province' => $request->province,
            'city_municipality' => $request->city_municipality
        ]);
        return back()->withErrors(['location' => 'Invalid province or city/municipality selected.'])->withInput();
    }

    // Validate the request data
    $validatedData = $request->validate([
        'province' => 'required|string|max:255',
        'city_municipality' => 'required|string|max:255',
        'program' => 'required|string|max:100',
        'target' => 'required|integer|min:0',
        'fund_allocation' => 'required|numeric|min:0',
    ]);

    // Convert fund_allocation to decimal
    $validatedData['fund_allocation'] = (float) $validatedData['fund_allocation'];

    // Override the province and city names with the PSGC codes
    $validatedData['province'] = $province->psgc;
    $validatedData['city_municipality'] = $city_municipality->psgc;
    $validatedData['program'] = $program->id;

    // Update the allocation with the validated data
    try {
        $allocation->update($validatedData);
        \Log::info('Allocation updated successfully for ID:', ['id' => $allocation->id]);  // Corrected log
    } catch (\Exception $e) {
        \Log::error('Error updating allocation:', ['error' => $e->getMessage()]);
        return back()->withErrors(['error' => 'Failed to update allocation.'])->withInput();
    }

    // Log the action of updating an allocation
    try {
        ClientLog::create([
            'user_id' => auth()->id(),
            'action' => 'edited',
            'type' => 'allocation',
            'record_id' => $allocation->id,
        ]);
        \Log::info('Client log entry created for allocation update:', ['id' => $allocation->id]);  // Corrected log
    } catch (\Exception $e) {
        \Log::error('Error creating client log:', ['error' => $e->getMessage()]);
        return back()->withErrors(['error' => 'Failed to log the update action.']);
    }

    // Redirect to client reports page with a success message
    return redirect()->route('client-reports')->with('success', 'Allocation updated successfully.');
}
 
public function getAllocations(Request $request)
{
    $user = Auth::user();
    $query = Allocation::with(['province', 'citymuni', 'program'])->where('program', $user->program);

    if ($request->has('date')) {
        $query->whereDate('created_at', $request->date);
    }

    $allocations = $query->get();

    return response()->json([
        'allocations' => $allocations,
    ]);
    }
}
