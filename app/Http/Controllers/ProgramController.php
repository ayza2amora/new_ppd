<?php
namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    public function show($id)
    {
        $program = Program::findOrFail($id);
        return response()->json([
            'name' => $program->name,
            'status' => $program->status, // Include the status here
            'logo' => $program->logo
        ]);
    }

    public function index()
    {
        $programs = Program::all();
        return Inertia::render('Programs/admin-programs', 
        ['programs' => $programs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:programs,name',
            'logo' => 'nullable|mimes:jpeg,jpg,png,gif|max:1024',  // Accept multiple image types
        ]);
    
        // Create the program
        $program = new Program;
        $program->name = $request->name;
        $program->status = $request->input('status', '0');
    
        // Handle logo upload
    if ($request->hasFile('logo')) {
        // Use original extension of the uploaded file
        $logoFile = $request->file('logo');
        $filename = time() . '.' . $logoFile->getClientOriginalExtension();  // Generate unique file name
        $logoPath = $logoFile->move(public_path('ppd-images'), $filename);  // Move to 'ppd-images'

        // Store the relative path in the database
        $program->logo = 'ppd-images/' . $filename;
    }

    $program->save();
    
        // Log action
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'create_program',
            'previous_value' => null,
            'new_value' => $program->name,
            
        ]);
    
        return redirect()->route('admin-programs')->with('success', 'Program created successfully.');
    }

    public function update(Request $request, Program $program)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:programs,name,' . $program->id,
            'logo' => 'nullable|image|max:1024',
        ]);
    
        // Store the original program name (before the update) for logging
        $originalName = $program->name;
        
        // Update the program
        $program->name = $request->name;
        $program->status = $request->input('status', '0');
    
        // Handle logo update (if any)
        if ($request->hasFile('logo')) {
            // Move the new logo to 'public/ppd-images' and store the relative path
            $logoPath = $request->file('logo')->move(public_path('ppd-images'), $request->file('logo')->getClientOriginalName());
            $program->logo = 'ppd-images/' . $request->file('logo')->getClientOriginalName();
        }
    
        $program->save();
    
        // Log the update action with the previous value (original name)
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'update_program',
            'previous_value' => $originalName,
            'new_value' => $request->name,
            'changed_by' => Auth::id(),
        ]);
    
        return redirect()->route('admin-programs')->with('success', 'Program updated successfully.');
    }
    

    public function updateStatus(Request $request, Program $program)
    {
        $request->validate([
            'status' => 'required|boolean',  // Ensure the status is either 0 (unrestricted) or 1 (restricted)
        ]);
    
        // Store the original status before the update
        $originalStatus = $program->status;
    
        // Update the program status
        $program->status = $request->status;
        $program->save();
    
        // Log the status change
        if ($originalStatus != $request->status) {
            $statusChange = ($request->status == 1) ? 'Restricted' : 'Unrestricted';
            $previousStatus = ($originalStatus == 1) ? 'Restricted' : 'Unrestricted';
    
            Log::create([
                'user_id' => Auth::id(),
                'action' => 'update_program_status',
                'previous_value' => $program->name . ' status changed from ' . $previousStatus,
                'new_value' => $statusChange,
            ]);
        }
    
        return redirect()->route('admin-programs')->with('success', 'Program status updated successfully.');
    }

    public function getPrograms()
    {
        $programs = Program::all();
        return Inertia::render('Auth/Register', 
        ['programs' => $programs]);
    }

    public function userPrograms()
    {
        $programs = Program::all();
        return Inertia::render('Admin/Admin-accounts', 
        ['programs' => $programs]);
    }

    public function allocationPrograms()
    {
       // Fetch all programs with status included
        $programs = Program::all(['id', 'name', 'status']);
        return Inertia::render('Client/AllocationForm', 
        ['programs' => $programs]);
    }

    public function utilizationPrograms()
    {
        $programs = Program::all();
        return Inertia::render('Client/UtilizationForm', 
        ['programs' => $programs]);
    }

    public function reportPrograms()
    {
        $programs = Program::all();
        return Inertia::render('Admin/Admin-reports', 
        ['programs' => $programs]);
    }
    
}

