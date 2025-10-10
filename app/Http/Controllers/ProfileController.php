<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Update profil user + upload foto profil ke public/images/avatars
     * dan simpan path relatifnya ke kolom users.photo_profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'firstname'     => ['nullable', 'string', 'max:255'],
            'lastname'      => ['nullable', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'photo_profile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo'  => ['nullable', 'boolean'],
        ]);

        // Update field dasar
        $user->name      = $validated['name'];
        $user->firstname = $validated['firstname'] ?? $user->firstname;
        $user->lastname  = $validated['lastname']  ?? $user->lastname;
        $user->phone     = $validated['phone']     ?? $user->phone;
        $user->email     = $validated['email'];

        // Hapus foto jika diminta
        if ($request->boolean('remove_photo')) {
            $this->deletePhotoIfExists($user->photo_profile);
            $user->photo_profile = null;
        }

        // Upload foto baru (jika ada)
        if ($request->hasFile('photo_profile')) {
            // Hapus foto lama terlebih dahulu
            $this->deletePhotoIfExists($user->photo_profile);

            $file = $request->file('photo_profile');
            $ext  = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $ext = 'jpg';
            }

            // Pastikan folder tersedia
            $destDir = public_path('images/avatars');
            if (! is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }

            // Nama file unik per user
            $filename = $user->id . '_' . date('YmdHis') . '.' . $ext;
            $file->move($destDir, $filename);

            // Simpan path relatif ke kolom
            $user->photo_profile = 'images/avatars/' . $filename;
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Hapus file lama jika ada (path relatif dari public/).
     */
    private function deletePhotoIfExists(?string $relativePath): void
    {
        if (!$relativePath) return;

        $fullPath = public_path($relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
