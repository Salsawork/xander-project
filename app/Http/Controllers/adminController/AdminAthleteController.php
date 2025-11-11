<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\AthleteDetail;
use App\Models\OrderSparring;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AthleteExport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminAthleteController extends Controller
{
    /**
     * Lokasi absolut folder gambar athlete (FE):
     * /home/xanderbilliard.site/public_html/images/athlete
     *
     * URL publik:
     * https://xanderbilliard.site/images/athlete/{filename}
     *
     * Di Blade:
     *   asset('images/athlete/'.$filename)
     */
    private function getFeAthleteDir(): string
    {
        return '/home/xanderbilliard.site/public_html/images/athlete';
    }

    /**
     * Upload 1 file foto athlete ke folder FE.
     * Yang disimpan di DB: hanya nama file.
     */
    private function uploadAthleteImage($file): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $fePath = $this->getFeAthleteDir();

        if (!File::exists($fePath)) {
            File::makeDirectory($fePath, 0755, true);
        }

        $ext      = strtolower($file->getClientOriginalExtension());
        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = Str::slug($origName) ?: 'athlete';
        $unique   = now()->format('YmdHis') . '-' . Str::random(6);

        $filename = "{$unique}-{$safeBase}.{$ext}";

        // Simpan langsung ke folder FE
        $file->move($fePath, $filename);

        return $filename;
    }

    /**
     * Hapus file foto athlete dari folder FE berdasarkan nama file di DB.
     */
    private function deleteAthleteImage(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $fePath = $this->getFeAthleteDir();
        $full   = $fePath . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($full)) {
            @unlink($full);
        }
    }

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

        $orderSparrings = OrderSparring::select('athlete_id', 'admin_fee')->get();

        $feeByAthlete = $orderSparrings
            ->groupBy('athlete_id')
            ->map(function ($group) {
                return $group->sum('admin_fee');
            });

        foreach ($athletes as $athlete) {
            $athlete->total_admin_fee = $feeByAthlete[$athlete->id] ?? 0;
        }

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
     * Upload foto ke /home/xanderbilliard.site/public_html/images/athlete
     * dan simpan hanya nama file di kolom image.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => 'required|string|min:8',
            'handicap'          => 'nullable|string|max:255',
            'experience_years'  => 'nullable|integer',
            'specialty'         => 'nullable|string|max:255',
            'location'          => 'nullable|string|max:255',
            'bio'               => 'nullable|string',
            'price_per_session' => 'nullable|numeric|min:0',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:4096',
        ]);

        try {
            DB::beginTransaction();

            // User baru dengan role athlete
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'roles'    => 'athlete',
            ]);

            // Upload foto (jika ada) → simpan filename
            $filename = null;
            if ($request->hasFile('image')) {
                $filename = $this->uploadAthleteImage($request->file('image'));
            }

            AthleteDetail::create([
                'user_id'           => $user->id,
                'handicap'          => $request->handicap,
                'experience_years'  => $request->experience_years,
                'specialty'         => $request->specialty,
                'location'          => $request->location,
                'bio'               => $request->bio,
                'price_per_session' => $request->price_per_session ?? 0,
                'image'             => $filename, // hanya nama file
            ]);

            DB::commit();

            return redirect()->route('athlete.index')->with('success', 'Athlete berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambah athlete', ['e' => $e]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified athlete.
     */
    public function edit(AthleteDetail $athlete)
    {
        $athlete->load('user');

        Log::info('Edit Athlete Data:', [
            'athlete_id'   => $athlete->id,
            'user_id'      => $athlete->user_id,
            'athlete_data' => $athlete->toArray(),
            'user_data'    => $athlete->user ? $athlete->user->toArray() : 'User not found',
        ]);

        return view('dash.admin.athlete.edit', compact('athlete'));
    }

    /**
     * Update the specified athlete in storage.
     * Jika upload foto baru, hapus file lama dari /images/athlete lalu simpan yang baru.
     */
    public function update(Request $request, AthleteDetail $athlete)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($athlete->user_id),
            ],
            'password'          => 'nullable|string|min:8',
            'handicap'          => 'nullable|string|max:255',
            'experience_years'  => 'nullable|integer',
            'specialty'         => 'nullable|string|max:255',
            'location'          => 'nullable|string|max:255',
            'bio'               => 'nullable|string',
            'price_per_session' => 'nullable|numeric|min:0',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:4096',
        ]);

        try {
            DB::beginTransaction();

            // Update user
            $user = User::findOrFail($athlete->user_id);
            $user->name  = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Upload foto baru jika ada
            if ($request->hasFile('image')) {
                // Hapus foto lama jika ada
                if (!empty($athlete->image)) {
                    $this->deleteAthleteImage($athlete->image);
                }

                $filename       = $this->uploadAthleteImage($request->file('image'));
                $athlete->image = $filename;
            }

            // Update detail athlete lain
            $athlete->handicap          = $request->handicap;
            $athlete->experience_years  = $request->experience_years;
            $athlete->specialty         = $request->specialty;
            $athlete->location          = $request->location;
            $athlete->bio               = $request->bio;
            $athlete->price_per_session = $request->price_per_session ?? 0;
            $athlete->save();

            DB::commit();

            return redirect()->route('athlete.index')->with('success', 'Data athlete berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update athlete', ['e' => $e]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified athlete from storage.
     * Sekaligus hapus file foto dari /images/athlete.
     */
    public function destroy(AthleteDetail $athlete)
    {
        try {
            DB::beginTransaction();

            // Hapus file foto fisik jika ada
            if (!empty($athlete->image)) {
                $this->deleteAthleteImage($athlete->image);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Hapus user; relasi cascade akan menghapus AthleteDetail jika diset
            User::where('id', $athlete->user_id)->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();

            return redirect()->route('athlete.index')->with('success', 'Athlete berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus athlete', ['e' => $e]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $filename = 'athletes_' . now()->format('Ymd_His') . '.xlsx';
        $search   = $request->get('search');

        return Excel::download(new AthleteExport($search), $filename);
    }

    public function showOrders($id)
    {
        $athlete = User::where('roles', 'athlete')->findOrFail($id);

        $orders = Order::where('order_type', 'sparring')
            ->whereHas('orderSparrings', function ($q) use ($id) {
                $q->where('athlete_id', $id);
            })
            ->when(request('search'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%')
                        ->orWhere('email', 'like', '%' . request('search') . '%');
                });
            })
            ->when(request('status'), function ($query) {
                $query->where('payment_status', request('status'));
            })
            ->orderBy('created_at', request('orderBy') === 'asc' ? 'asc' : 'desc')
            ->get();

        return view('dash.admin.athlete.detail', compact('athlete', 'orders'));
    }

    public function verifyPayment(Request $request, $orderId)
    {
        $order = Order::with('orderSparrings')->findOrFail($orderId);

        // Set status jadi paid
        $order->update(['payment_status' => 'paid']);

        $totalAdminFee = 0;

        foreach ($order->orderSparrings as $sparring) {
            $price          = $sparring->price;
            $adminFee       = $price * 0.10;
            $athleteEarning = $price - $adminFee;

            $sparring->update([
                'admin_fee'       => $adminFee,
                'athlete_earning' => $athleteEarning,
            ]);

            $totalAdminFee += $adminFee;
        }

        return back()->with(
            'success',
            'Payment verified. Total fee admin: Rp ' . number_format($totalAdminFee, 0, ',', '.')
        );
    }
}
