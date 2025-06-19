<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Process the signup request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return redirect()->intended('login');
    }

    /**
     * Process the login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Redirect berdasarkan role user
            $user = Auth::user();
            if ($user->roles === 'admin') {
                return redirect()->route('dashboard');
            } elseif ($user->roles === 'venue') {
                return redirect()->route('venue.dashboard');
            } elseif ($user->roles === 'athlete') {
                return redirect()->route('athlete.dashboard');
            } else {
                // Default untuk user biasa
                return redirect()->route('dashboard');
            }
        }

        return back()
            ->withInput()
            ->with('error', 'The provided credentials do not match our records.');
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
}
