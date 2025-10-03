<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
class LoginController extends Controller
{
   

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $otp = rand(100000, 999999); // OTP 6 digit

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $request->phone ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 0,
            'otp_code' => $otp,
        ]);

        // Kirim email OTP
        Mail::raw("Your verification code is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Email Verification Code');
        });

        return redirect()->route('verification.form', ['email' => $user->email])
                        ->with('success', 'Verification code sent to your email.');
    }

     /**
     * Process the signup request.
     */
    
    public function showVerificationForm(Request $request)
    {
        $email = $request->query('email') ?? $request->query('email'); // aman
        return view('auth.verify', compact('email'));
    }
    
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)
                    ->where('otp_code', $request->otp_code)
                    ->first();

        if (!$user) {
            return back()->withErrors(['otp_code' => 'Invalid OTP code.']);
        }

        $user->update([
            'status' => 1,
            'otp_code' => null, // reset setelah berhasil
        ]);

        return redirect()->route('login')->with('success', 'Email verified! You can now login.');
    }

    /**
     * Process the login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required'], // bisa email atau phone
            'password' => ['required'],
        ]);

        $field = is_numeric($credentials['login']) ? 'phone' : 'email';

        if (Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->roles === 'admin') {
                return redirect()->route('dashboard');
            } elseif ($user->roles === 'venue') {
                return redirect()->route('venue.index');
            } elseif ($user->roles === 'athlete') {
                return redirect()->route('athlete.dashboard');
            } else {
                return redirect()->route('dashboard');
            }
        }

        return back()->withInput()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ]);
    }
    /**
     * Process the logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function updateProfile(Request $request)
    {
        auth()->user()->update($request->only('name', 'email'));
        return back()->with('success', 'Profile updated successfully');
    }

}
