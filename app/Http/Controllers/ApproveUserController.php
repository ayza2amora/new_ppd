<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth; // Correct Auth facade import

class ApproveUserController extends Controller
{
    public function approve($userId)
    {
        // Find the user being approved
        $user = User::findOrFail($userId);

        // Approve the user
        $user->approved = 1; // Set approved to 1 for approved
        $user->save();

        // Log the approval action with the user's full name and previous status ("pending")
        $userFullName = $user->first_name . ' ' . $user->last_name;

        Log::create([
            'user_id' => Auth::id(),  // The user performing the action (admin)
            'action' => 'approved',
            'previous_value' => 'User ' . $userFullName . ' was pending approval',
            'new_value' => 'approved',
        ]);

        return response()->json(['message' => 'User approved successfully'], 200);
    }

    public function pending($userId)
    {
        // Find the user being set to pending
        $user = User::findOrFail($userId);
        
        // Set approved to 0 for pending
        $user->approved = 0; 
        $user->save();

        return response()->json(['message' => 'User is pending'], 200);
    }

    public function decline($userId)
    {
        // Find the user being declined
        $user = User::findOrFail($userId);

        // Set approved to -1 for declined (or you can create a separate 'declined' column if preferred)
        $user->approved = -1;
        $user->save();

        // Log the decline action with the user's full name and previous status ("pending" or "approved")
        $userFullName = $user->first_name . ' ' . $user->last_name;

        Log::create([
            'user_id' => Auth::id(),  // The user performing the action (admin)
            'action' => 'declined',
            'previous_value' => 'User ' . $userFullName . ' was pending approval',
            'new_value' => 'declined',
        ]);

        return response()->json(['message' => 'User declined successfully'], 200);
    }
}
