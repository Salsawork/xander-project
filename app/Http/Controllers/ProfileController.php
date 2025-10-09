<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil.
     */
    public function edit()
    {
        return view('dash.user.profile');
    }

    /**
     * Update profil user + upload avatar (tanpa kolom DB).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname'  => ['nullable', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        // Update data dasar yang memang ada di tabel
        $user->fill([
            'name'      => $validated['name'],
            'firstname' => $validated['firstname'] ?? $user->firstname,
            'lastname'  => $validated['lastname']  ?? $user->lastname,
            'phone'     => $validated['phone']     ?? $user->phone,
            'email'     => $validated['email'],
        ])->save();

        // Handle hapus avatar jika diminta
        if ($request->boolean('remove_avatar')) {
            $this->deleteAllAvatarVariants($user->id);
        }

        // Handle upload avatar baru
        if ($request->hasFile('avatar')) {
            $this->deleteAllAvatarVariants($user->id);
            $file = $request->file('avatar');
            $ext  = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $ext = 'jpg';
            }
            $filename = $user->id . '.' . $ext;
            Storage::disk('public')->putFileAs('avatars', $file, $filename);
        }

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Hapus semua kemungkinan file avatar user berdasarkan id.
     */
    private function deleteAllAvatarVariants(int $userId): void
    {
        $exts = ['jpg', 'jpeg', 'png', 'webp'];
        foreach ($exts as $ext) {
            $path = "avatars/{$userId}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
