<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\AthleteDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminAthleteController extends Controller
{
    /**
     * Display a listing of the athletes.
     */
  public function index()
{
    $athletes = User::where('roles', 'athlete')
        ->with('athleteDetail')
        ->when(request('search'), function ($query) {
            $search = request('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('athleteDetail', function ($q) use ($search) {
                    $q->where('specialty', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
        })
        ->get();

    return view('dash.admin.athlete.index', compact('athletes'));
}

    /**
     * Show the form for creating a new athlete.
     */
    public function create()
    {
        return view('dash.admin.athlete.create');
    }

    /**
     * Store a newly created athlete in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'handicap' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer',
            'specialty' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'price_per_session' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Buat user baru dengan role athlete
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roles' => 'athlete',
            ]);

            // Proses upload gambar jika ada
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('athletes', 'public');
            }

            // Buat athlete detail baru terkait dengan user
            AthleteDetail::create([
                'user_id' => $user->id,
                'handicap' => $request->handicap,
                'experience_years' => $request->experience_years,
                'specialty' => $request->specialty,
                'location' => $request->location,
                'bio' => $request->bio,
                'price_per_session' => $request->price_per_session ?? 0,
                'image' => $imagePath,
            ]);

            // Commit transaksi
            DB::commit();

            return redirect()->route('athlete.index')->with('success', 'Athlete berhasil ditambahkan!');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified athlete.
     */
    public function edit(AthleteDetail $athlete)
    {
        // Load relasi user
        $athlete->load('user');
        
        // Tambahkan log untuk debugging
        Log::info('Edit Athlete Data:', [
            'athlete_id' => $athlete->id,
            'user_id' => $athlete->user_id,
            'athlete_data' => $athlete->toArray(),
            'user_data' => $athlete->user ? $athlete->user->toArray() : 'User not found'
        ]);
        
        return view('dash.admin.athlete.edit', compact('athlete'));
    }

    /**
     * Update the specified athlete in storage.
     */
    public function update(Request $request, AthleteDetail $athlete)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($athlete->user_id),
            ],
            'password' => 'nullable|string|min:8',
            'handicap' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer',
            'specialty' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'price_per_session' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Update data user
            $user = User::find($athlete->user_id);
            $user->name = $request->name;
            $user->email = $request->email;
            
            // Update password jika diisi
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();

            // Proses upload gambar jika ada
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($athlete->image && Storage::disk('public')->exists($athlete->image)) {
                    Storage::disk('public')->delete($athlete->image);
                }
                
                // Upload gambar baru
                $imagePath = $request->file('image')->store('athletes', 'public');
                $athlete->image = $imagePath;
            }

            // Update athlete detail
            $athlete->handicap = $request->handicap;
            $athlete->experience_years = $request->experience_years;
            $athlete->specialty = $request->specialty;
            $athlete->location = $request->location;
            $athlete->bio = $request->bio;
            $athlete->price_per_session = $request->price_per_session ?? 0;
            $athlete->save();

            // Commit transaksi
            DB::commit();

            return redirect()->route('athlete.index')->with('success', 'Data athlete berhasil diperbarui!');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified athlete from storage.
     */
    public function destroy(AthleteDetail $athlete)
    {
        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Hapus gambar jika ada
            if ($athlete->image && Storage::disk('public')->exists($athlete->image)) {
                Storage::disk('public')->delete($athlete->image);
            }

            // Nonaktifkan foreign key checks sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Hapus user (akan otomatis menghapus athlete detail karena relasi cascade)
            User::where('id', $athlete->user_id)->delete();
            
            // Aktifkan kembali foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Commit transaksi
            DB::commit();

            return redirect()->route('athlete.index')->with('success', 'Athlete berhasil dihapus!');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}