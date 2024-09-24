<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Utilization;
use App\Models\Province;
use App\Models\CityMuni;
use App\Models\Program;
use App\Models\ClientLog; // Import ClientLog model
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function index()
    {
        // Eager load province, cityMuni, and program relationships
        $utilizations = Utilization::with(['province', 'citymuni', 'program'])->get();

        // Transform the data to include province and city/municipality names
        $utilizations = $utilizations->map(function ($utilization) {
            return [
                'id' => $utilization->id,
                'province' => $utilization->province->col_province ?? 'Unknown Province',  // Get province name
                'city_municipality' => $utilization->cityMuni->col_citymuni ?? 'Unknown City/Municipality',  // Get city/municipality name
                'program' => $utilization->program->name ?? 'Unknown Program',  // Get program name
                'physical' => $utilization->physical,
                'fund_utilized' => $utilization->fund_utilized,
                'created_at' => $utilization->created_at,
                'updated_at' => $utilization->updated_at,
            ];
        });

        return response()->json(['utilizations' => $utilizations]);
    }

    public function create()
    {
        $provinces = Province::all();
        $programs = Program::all();
    
        return Inertia::render('Client/UtilizationForm', [
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
    
        if ($program->status == 1) {
            return back()->withErrors(['program' => 'You cannot submit a utilization for a restricted program.'])->withInput();
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
            'physical' => 'required|integer|min:0',
            'fund_utilized' => 'required|numeric|min:0',
        ]);
    
        // Override the province and city names with the PSGC codes
        $validatedData['province'] = $province->psgc;
        $validatedData['city_municipality'] = $city_municipality->psgc;
        $validatedData['program'] = $program->id; // Replace program name with its ID
    
        // Create a new utilization record using PSGC codes and program ID
        $utilization = Utilization::create($validatedData);
    
        \Log::info('Validated data:', $validatedData);
    
        // Log the action of creating a new utilization
        ClientLog::create([
            'user_id' => auth()->id(),
            'action' => 'added',
            'type' => 'utilization',
            'record_id' => $utilization->id, // The new utilization's ID
        ]);
    
        // Redirect to client reports page with a success message
        return Inertia::location(route('client-reports'));
    }    
    public function update(Request $request, Utilization $utilization)
    {
        \Log::info('Update Request received for Utilization:', $request->all());
    
        // Fetch the program by name and check if it's restricted
        $program = Program::where('name', $request->program)->first();
    
        if (!$program) {
            \Log::error('Program not found:', ['program' => $request->program]);
            return back()->withErrors(['program' => 'Invalid program selected.'])->withInput();
        }
    
        if ($program->status == 1) {
            \Log::error('Program is restricted:', ['program' => $program->name]);
            return back()->withErrors(['program' => 'You cannot update a utilization for a restricted program.'])->withInput();
        }
    
        // Fetch the PSGC codes for province and city_municipality based on names
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
            'physical' => 'required|integer|min:0',
            'fund_utilized' => 'required|numeric|min:0',
        ]);
    
        // Convert fund_utilized to decimal
        $validatedData['fund_utilized'] = (float) $validatedData['fund_utilized'];
    
        // Override the province and city names with the PSGC codes
        $validatedData['province'] = $province->psgc;
        $validatedData['city_municipality'] = $city_municipality->psgc;
        $validatedData['program'] = $program->id;
    
        // Update the utilization with the validated data
        try {
            $utilization->update($validatedData);
            \Log::info('Utilization updated successfully for ID:', ['id' => $utilization->id]);  // Corrected log
        } catch (\Exception $e) {
            \Log::error('Error updating utilization:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to update utilization.'])->withInput();
        }
    
        // Log the action of updating a utilization
        try {
            ClientLog::create([
                'user_id' => auth()->id(),
                'action' => 'edited',
                'type' => 'utilization',
                'record_id' => $utilization->id,
            ]);
            \Log::info('Client log entry created for utilization update:', ['id' => $utilization->id]);  // Corrected log
        } catch (\Exception $e) {
            \Log::error('Error creating client log:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to log the update action.']);
        }
    
        // Redirect to client reports page with a success message
        return redirect()->route('client-reports')->with('success', 'Utilization updated successfully.');
    }
    
    public function getUtilizations(Request $request)
{
    $user = Auth::user();
    $query = Utilization::with(['province', 'citymuni', 'program'])->where('program', $user->program);

    if ($request->has('date')) {
        $query->whereDate('created_at', $request->date);
    }

    $utilizations = $query->get();

    return response()->json([
        'utilizations' => $utilizations,
    ]);
    }
}

