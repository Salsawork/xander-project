<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminVenueController extends Controller
{
    /**
     * Display a listing of the venues.
     */
    public function index()
    {
        $venues = Venue::with('user')
            ->when(request('search'), function ($query) {
                $search = request('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->get();
    
        return view('dash.admin.venue.index', compact('venues'));
    }
    

    /**
     * Show the form for creating a new venue.
     */
    public function create()
    {
        return view('dash.admin.venue.create');
    }

    /**
     * Store a newly created venue in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'venue_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'operating_hours' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Buat user baru dengan role venue
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'venue',
            ]);

            // Buat venue baru terkait dengan user
            Venue::create([
                'user_id' => $user->id,
                'name' => $request->venue_name,
                'address' => $request->address,
                'phone' => $request->phone,
                'operating_hours' => $request->operating_hours,
                'description' => $request->description,
                'rating' => 0, // Default rating
            ]);

            // Commit transaksi
            DB::commit();

            return redirect()->route('venue.index')
                ->with('success', 'Venue berhasil ditambahkan!');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada error
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified venue.
     */
    public function edit(Venue $venue)
    {
        return view('dash.admin.venue.edit', compact('venue'));
    }

    /**
     * Update the specified venue in storage.
     */
    public function update(Request $request, Venue $venue)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($venue->user_id),
            ],
            'password' => 'nullable|string|min:8',
            'venue_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'operating_hours' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Update data user
            $user = User::find($venue->user_id);
            $user->name = $request->name;
            $user->username = $request->username;

            // Update password jika diisi
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Update data venue
            $venue->name = $request->venue_name;
            $venue->address = $request->address;
            $venue->phone = $request->phone;
            $venue->operating_hours = $request->operating_hours;
            $venue->description = $request->description;
            $venue->save();

            // Commit transaksi
            DB::commit();

            return redirect()->route('venue.index')
                ->with('success', 'Venue berhasil diperbarui!');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada error
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified venue from storage.
     */
    public function destroy(Venue $venue)
    {
        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Ambil user_id sebelum menghapus venue
            $userId = $venue->user_id;

            // Nonaktifkan foreign key checks sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Hapus venue
            $venue->delete();

            // Hapus user terkait
            User::destroy($userId);

            // Aktifkan kembali foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Commit transaksi
            DB::commit();

            return redirect()->route('venue.index')
                ->with('success', 'Venue berhasil dihapus!');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada error
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
