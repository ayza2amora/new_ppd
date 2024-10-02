<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OtpController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/OTP');
    }

    public function send(Request $request)
    {
        Log::info('OTP send method called');
        
        $user = Auth::user();

        // Check if OTP was recently sent by checking both the session and the database
        if (!$this->hasRecentOtp($user)) {
            try {
                Log::info('Attempting to generate and send OTP');
                $this->generateAndSendOtp($user); // Generate and send the OTP
                Session::put('otp_sent', true);
                Session::put('otp_expires_at', now()->addMinutes(5)); // Store expiry in session as well for redundancy
                Log::info('OTP sent successfully');
                return response()->json([
                    'message' => 'An OTP has been sent to your email address.'
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send OTP: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Failed to send OTP. Please try again later.'
                ], 500);
            }
        } else {
            Log::info('OTP already sent');
            return response()->json([
                'message' => 'An OTP has already been sent to your email address.'
            ]);
        }
    }

    public function verify(Request $request)
    {
        $otp = is_array($request->otp) ? implode('', $request->otp) : $request->otp;

        $request->merge(['otp' => $otp]);

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if (empty($otp)) {
            return back()->withErrors([
                'otp' => 'Please enter the OTP.'
            ]);
        }

        $user = $this->getAuthenticatedUser();
        $guard = session('auth_guard', 'web');
        $user = Auth::guard($guard)->user();

        if ($this->verifyOtpLogic($user, $otp)) {
            $user->otp_verified_at = now();
            $user->save();

            // Clear the session data once OTP is verified
            Session::forget('otp_sent');
            Session::forget('otp_expires_at');

            return $this->redirectToDashboard($user);
        }

        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }

    private function getAuthenticatedUser()
    {
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        } elseif (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }

        throw new \Exception('No authenticated user found');
    }

    public function resend()
    {
        $user = Auth::user();

        // Check if OTP was recently sent by both session and database
        if ($this->hasRecentOtp($user)) {
            return response()->json([
                'success' => false,
                'message' => 'An OTP was already sent recently. Please wait before requesting a new one.'
            ], 429);
        }

        try {
            $this->generateAndSendOtp($user);
            Session::put('otp_sent', true);
            Session::put('otp_expires_at', now()->addMinutes(5));

            return response()->json([
                'success' => true,
                'message' => 'OTP has been resent to your email.'
            ]);
        } catch (\Exception $e) {
            Log::error('OTP resend failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP. Please try again later.'
            ], 500);
        }
    }

    private function generateAndSendOtp(User $user)
    {
        Log::info('Generating OTP for user: ' . $user->id);
        $otp = sprintf('%06d', mt_rand(0, 999999));
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5); // Store OTP and expiration in the database
        $user->save();

        Log::info('Sending OTP email');
        Mail::to($user->email)->send(new OtpMail($otp));
        Log::info('OTP email sent');
    }

    private function verifyOtpLogic(User $user, $otp)
    {
        return $user->otp === $otp && $user->otp_expires_at > now();
    }

    private function hasRecentOtp(User $user)
    {
        // Check both session and database if OTP was recently sent
        return (Session::has('otp_expires_at') && Session::get('otp_expires_at') > now()) ||
               ($user->otp_expires_at && $user->otp_expires_at > now());
    }

    private function redirectToDashboard($user)
    {
        if ($user instanceof User) {
            if ($user->role === '0') { // Change here: 0 for 'user'
                return redirect()->intended(route('client-reports'))
                    ->with('message', 'OTP verified successfully')
                    ->with('redirectTo', route('client-reports'));
            }
            if ($user->role === '1') { // Change here: 1 for 'admin'
                return redirect()->intended(route('admin-dashboard'))
                    ->with('message', 'OTP verified successfully')
                    ->with('redirectTo', route('admin-dashboard'));
            }
        }
    
        Log::error('Unable to determine dashboard for user: ' . $user->id);
        return redirect()->route('login')
            ->with('error', 'Unable to access dashboard. Please contact support.')
            ->with('redirectTo', route('login'));
    }
}
