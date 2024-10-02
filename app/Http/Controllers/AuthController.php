<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\Log;

class AuthController extends Controller
{public function index()
    {
        // Use 'with' to eager load the 'program' relation
        $users = User::with('program')->get();
    
        return response()->json($users);
    }

    public function showRegistrationForm()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        // Validation
        $fields = $request->validate([
            'first_name' => ['required', 'max:50'],
            'middle_name' => ['nullable', 'max:20'],
            'last_name' => ['required', 'max:20'],
            'suffix' => ['nullable', 'max:10'],
            'role' => 'required|integer|in:0,1',
            'email' => ['required', 'email', 'max:50', 'unique:users'],
            'employeeid' => ['required', 'max:8', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'program' => $request->role === '0' ? ['nullable', 'max:100'] : ['nullable', 'max:100'],
        ]);

    
        // Find the program ID based on the program name
        $program = \App\Models\Program::where('name', $fields['program'])->first();
        $program_id = $program ? $program->id : null;
    
        $user = User::create([
            'first_name' => $fields['first_name'],
            'middle_name' => $fields['middle_name'] ?? null,
            'last_name' => $fields['last_name'],
            'suffix' => $fields['suffix'] ?? null,
            'role' => $fields['role'],
            'email' => $fields['email'],
            'employeeid' => $fields['employeeid'],
            'password' => bcrypt($fields['password']),
            'program' => $fields['role'] === '0' ? $program_id : null, // Store the program ID
        ]);
    
        return redirect()->route('login')->with('success', 'Account created successfully. Please wait for Admin approval.');
    }
    

    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided email does not match any account.',
            ])->withInput();
        }

        if (!Auth::attempt($fields)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ])->withInput();
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if (!$user->approved) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account has not been approved yet. Please contact the administrator.',
            ])->withInput();
        }

        if (!$user->active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account is deactivated. Please contact the administrator.',
            ])->withInput();
        }

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otp));

        return redirect()->route('otp.show')->with('success', 'OTP has been sent to your email.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function activate($id)
    {
        try {
            // Fetch the user being activated
            $user = User::findOrFail($id);
    
            // Check if the user is approved before activation
            if (!$user->approved) {
                return response()->json(['error' => 'User must be approved before activation'], 403);
            }
    
            $previousActiveStatus = $user->active;  // Store the original active status
    
            // Activate the user
            $user->active = true;
            $user->save();
    
            // Log the activation with the user's full name, user ID, and previous status
            $userFullName = $user->first_name . ' ' . $user->last_name;
    
            Log::create([
                'user_id' => Auth::id(),  // ID of the user who is performing the action
                'action' => 'activate',
                'previous_value' => 'User ' . $userFullName . ' was ' . ($previousActiveStatus ? 'active' : 'inactive'),
                'new_value' => 'active',
            ]);
    
            return response()->json(['message' => 'User activated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User activation failed'], 500);
        }
    }

    public function deactivate($id)
    {
        try {
            // Fetch the user being deactivated
            $user = User::findOrFail($id);
            $previousActiveStatus = $user->active;
    
            // Deactivate the user
            $user->active = false;
            $user->save();
    
            // Log the deactivation with the user's full name and previous status (no ID)
            $userFullName = $user->first_name . ' ' . $user->last_name;
    
            Log::create([
                'user_id' => Auth::id(),  // ID of the user who is performing the action
                'action' => 'deactivate',
                'previous_value' => 'User ' . $userFullName . ' was ' . ($previousActiveStatus ? 'active' : 'inactive'),
                'new_value' => 'inactive',
            ]);
    
            return response()->json(['message' => 'User deactivated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User deactivation failed'], 500);
        }
    }
    

    public function getAuthenticatedUser()
    {
        // Get the currently authenticated user
        $user = Auth::user();
    
        // Find the program based on the program ID in the user's table
        $program = \App\Models\Program::find($user->program);
        
        // Return the user's information, including program details
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'employeeid' => $user->employeeid,
            'role' => $user->role,
            'program' => $program ? $program->name : 'Unknown Program', // Program name
            'program_logo' => $program ? $program->logo : '', // Program logo
            'program_status' => $program ? $program->status : 0, // Program status (0 = unrestricted, 1 = restricted)
            'approved' => $user->approved,
            'active' => $user->active,
        ]);
    }

public function updateRole(Request $request, $id)
{
    $user = User::find($id);
    if ($user) {
        $previousRole = $user->role;
        $newRole = $request->input('role');

        // Update the user's role
        if (in_array($newRole, [0, 1])) {
            $user->role = $newRole;
            $user->save();
        }
        // Log role change with the user's full name
        $userFullName = $user->first_name . ' ' . $user->last_name;

        Log::create([
            'user_id' => Auth::id(),  // ID of the user who is performing the action
            'action' => 'role_change',
            'previous_value' => 'User ' . $userFullName . ' had role ' . $previousRole,
            'new_value' => $newRole,
        ]);

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 404);
}

public function updateProgram(Request $request, $id)
{
    try {
        // Validate that the program input is an integer (ID of the program)
        $request->validate([
            'program' => 'required|integer',  // Ensure the program ID is an integer
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Store the previous program before updating
        $previousProgram = $user->program ? \App\Models\Program::find($user->program) : null;
        $previousProgramName = $previousProgram ? $previousProgram->name : 'No Program Assigned';

        // Update the user's program by assigning the new program ID
        $user->program = $request->input('program');
        $user->save();  // Use save() to update an existing record, not insert()

        // Load the updated program relation for the user
        $user->load('program');

        // Find the new program for logging purposes
        $newProgram = \App\Models\Program::find($request->input('program'));
        $newProgramName = $newProgram ? $newProgram->name : 'Unknown Program';

        // Log the program change with the user's full name
        $userFullName = $user->first_name . ' ' . $user->last_name;

        Log::create([
            'user_id' => Auth::id(),  // ID of the admin performing the action
            'action' => 'program_change',
            'previous_value' => 'User ' . $userFullName . ' was in program ' . $previousProgramName,
            'new_value' => 'User ' . $userFullName . ' is now in program ' . $newProgramName,
        ]);

        return response()->json($user);  // Return the updated user as JSON

    } catch (\Exception $e) {
        // Catch any errors and return them as a JSON response
        return response()->json(['error' => 'Program update failed', 'message' => $e->getMessage()], 500);
    }
}




}


    
