<?php
// app/Http/Controllers/Auth/GoogleController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        // Arahkan user ke halaman consent Google
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Google login gagal: '.$e->getMessage());
        }

        $email = $googleUser->getEmail();

        // Wajib ada email agar bisa dipetakan ke username
        if (!$email) {
            return redirect()->route('login')->with('error', 'Akun Google tidak mengembalikan email.');
        }

        // Cari pengguna by username = email (skema kamu: username UNIQUE)
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Pecah nama jadi first/last sederhana
            $fullName = $googleUser->getName() ?: ($googleUser->getNickname() ?: $email);
            [$first, $last] = $this->splitName($fullName);

            $user = User::create([
                'name'      => $fullName,
                'firstname' => $first ?: 'firstname',
                'lastname'  => $last  ?: 'lastname',
                'email'     => $email,        // kolom email kamu tidak unique — tetap boleh diisi
                // 'username'  => $email,        // ← kunci unik di tabel kamu
                'password'  => bcrypt(Str::random(40)), // tidak dipakai utk OAuth
                'roles'     => 'user',        // default role
            ]);
        } else {
            // Sinkronisasi nama/email (opsional)
            $user->forceFill([
                'name'  => $googleUser->getName() ?: $user->name,
                'email' => $email,
            ])->save();
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended('/dashboard'); // sesuaikan tujuanmu
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name));
        if (!$parts || count($parts) === 0) return [null, null];
        $first = array_shift($parts);
        $last  = count($parts) ? implode(' ', $parts) : null;
        return [$first, $last];
    }
}
