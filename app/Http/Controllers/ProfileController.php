<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Venue;
use App\Models\AthleteDetail;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $athlete = AthleteDetail::where('user_id', $user->id)->first();
        $venue = Venue::where('user_id', $user->id)->first();

        return view('dash.user.profile', compact('user', 'athlete', 'venue'));
    }

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

            // Optional (athlete / venue)
            'handicap'          => ['nullable', 'string', 'max:255'],
            'experience_years'  => ['nullable', 'numeric'],
            'specialty'         => ['nullable', 'string', 'max:255'],
            'location'          => ['nullable', 'string', 'max:255'],
            'bio'               => ['nullable', 'string', 'max:2000'],
            'price_per_session' => ['nullable', 'numeric'],

            'address'           => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'operating_hours'   => ['nullable', 'string', 'max:255'],
            'price'             => ['nullable', 'numeric'],
        ]);

        $user->fill([
            'name'      => $validated['name'],
            'firstname' => $validated['firstname'] ?? $user->firstname,
            'lastname'  => $validated['lastname'] ?? $user->lastname,
            'phone'     => $validated['phone'] ?? $user->phone,
            'email'     => $validated['email'],
        ]);

        // === 🔹 Folder tujuan upload di luar project Laravel ===
        // Ganti path di bawah ini sesuai lokasi real folder demo-xanders kamu.
        $externalAvatarDir = base_path('../demo-xanders/images/avatars'); // naik 1 level keluar project

        // Handle avatar
        if ($request->boolean('remove_photo')) {
            $this->deletePhotoIfExists($user->photo_profile, $externalAvatarDir);
            $user->photo_profile = null;
        } elseif ($request->hasFile('photo_profile')) {
            $this->deletePhotoIfExists($user->photo_profile, $externalAvatarDir);

            $file = $request->file('photo_profile');
            if (!is_dir($externalAvatarDir)) {
                @mkdir($externalAvatarDir, 0755, true);
            }

            $filename = $user->id . '_' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($externalAvatarDir, $filename);

            // Simpan hanya path relatif untuk akses di web (bukan absolute path server)
            $user->photo_profile = $filename;
        }

        $user->save();

        // Athlete
        $athlete = AthleteDetail::where('user_id', $user->id)->first();
        if ($athlete) {
            $athlete->update([
                'handicap'          => $validated['handicap'] ?? $athlete->handicap,
                'experience_years'  => $validated['experience_years'] ?? $athlete->experience_years,
                'specialty'         => $validated['specialty'] ?? $athlete->specialty,
                'location'          => $validated['location'] ?? $athlete->location,
                'bio'               => $validated['bio'] ?? $athlete->bio,
                'price_per_session' => $validated['price_per_session'] ?? $athlete->price_per_session,
            ]);
        }

        // Venue
        $venue = Venue::where('user_id', $user->id)->first();
        if ($venue) {
            $venue->update([
                'address'         => $validated['address'] ?? $venue->address,
                'description'     => $validated['description'] ?? $venue->description,
                'operating_hours' => $validated['operating_hours'] ?? $venue->operating_hours,
                'price'           => $validated['price'] ?? $venue->price,
            ]);
        }

        return back()->with('success', 'Profile updated successfully');
    }

    private function deletePhotoIfExists(?string $relativePath, string $baseDir): void
    {
        if (!$relativePath) return;

        $filename = basename($relativePath);
        $fullPath = rtrim($baseDir, '/') . '/' . $filename;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
