<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use App\Models\Order;
use App\Models\PriceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;                // upload helper
use Illuminate\Support\Facades\File;       // file helper

class AdminVenueController extends Controller
{
    /**
     * ================= Upload Helper (CMS + FE) =================
     * - CMS : public/images/venue
     * - FE  : ../demo-xanders/images/venue
     */
    private function uploadVenueFile(\Illuminate\Http\UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName     = preg_replace('/[^a-zA-Z0-9-_]/', '', Str::slug($originalName));
        $filename     = time() . '-' . $safeName . '.' . $file->getClientOriginalExtension();

        $cmsPath = public_path('images/venue');                      // public/images/venue
        $fePath  = base_path('../demo-xanders/images/venue');        // ../demo-xanders/images/venue

        if (!File::exists($cmsPath)) File::makeDirectory($cmsPath, 0755, true);
        if (!File::exists($fePath))  File::makeDirectory($fePath, 0755, true);

        // simpan ke CMS
        $file->move($cmsPath, $filename);

        // copy ke FE
        @copy($cmsPath . DIRECTORY_SEPARATOR . $filename, $fePath . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    private function deleteVenueFile(?string $filename): void
    {
        if (!$filename) return;

        $cmsPath = public_path('images/venue/' . $filename);
        $fePath  = base_path('../demo-xanders/images/venue/' . $filename);

        if (File::exists($cmsPath)) @unlink($cmsPath);
        if (File::exists($fePath))  @unlink($fePath);
    }

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
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'password'        => 'required|string|min:8',
            'venue_name'      => 'required|string|max:255',
            'address'         => 'required|string',
            'phone'           => 'required|string|max:20',
            'operating_hour'  => 'required|string|max:100',
            'closing_hour'  => 'required|string|max:100',
            'description'     => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $request->validate([
            'operating_hour' => function ($attribute, $value, $fail) use ($request) {
                if (strtotime($value) >= strtotime($request->closing_hour)) {
                    $fail('Jam operasional harus lebih awal dari jam tutup.');
                }
            },
        ]);

        try {
            DB::beginTransaction();

            // Buat user baru
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'roles'    => 'venue',
            ]);

            // Upload gambar jika ada (dukung 'image' atau 'gambar')
            $imageName = null;
            $file = $request->file('image') ?? $request->file('gambar');
            if ($file) {
                $imageName = $this->uploadVenueFile($file); // simpan CMS + copy FE, auto-buat folder
            }

            // Simpan data venue
            $venue = Venue::create([
                'user_id'        => $user->id,
                'name'           => $request->venue_name,
                'address'        => $request->address,
                'phone'          => $request->phone,
                'operating_hour' => $request->operating_hour,
                'closing_hour' => $request->closing_hour,
                'description'    => $request->description,
                'rating'         => 0,
                'image'          => $imageName,
            ]);

            PriceSchedule::create([
                'venue_id'        => $venue->id,
                'name'            => 'Reguler AllDay from Admin',
                'price'           => 0,
                'start_time'      => $request->operating_hour,
                'end_time'        => $request->closing_hour,
                'days'            => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'tables_applicable' => json_encode([]),
                'is_active'       => true,
            ]);

            DB::commit();

            return redirect()->route('venue.index')
                ->with('success', 'Venue berhasil ditambahkan!');
        } catch (\Exception $e) {
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
            'name'            => 'required|string|max:255',
            'email'           => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($venue->user_id),
            ],
            'password'        => 'nullable|string|min:8',
            'venue_name'      => 'required|string|max:255',
            'address'         => 'required|string',
            'phone'           => 'required|string|max:20',
            'operating_hour' => 'required|string|max:100',
            'closing_hour' => 'required|string|max:100',
            'description'     => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $request->validate([
            'operating_hour' => function ($attribute, $value, $fail) use ($request) {
                if (strtotime($value) >= strtotime($request->closing_hour)) {
                    $fail('Jam operasional harus lebih awal dari jam tutup.');
                }
            },
        ]);

        try {
            DB::beginTransaction();

            // Update user
            $user = User::findOrFail($venue->user_id);
            $user->name  = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Upload gambar baru jika ada (dukung 'image' atau 'gambar')
            $file = $request->file('image') ?? $request->file('gambar');
            if ($file) {
                // Hapus file lama di CMS + FE
                if (!empty($venue->image)) {
                    $this->deleteVenueFile($venue->image);
                }
                // Upload baru
                $venue->image = $this->uploadVenueFile($file);
            }

            // Update data venue
            $venue->name            = $request->venue_name;
            $venue->address         = $request->address;
            $venue->phone           = $request->phone;
            $venue->operating_hour = $request->operating_hour;
            $venue->closing_hour = $request->closing_hour;
            $venue->description     = $request->description;
            $venue->save();

            $priceSchedules = PriceSchedule::where('venue_id', $venue->id)->get();

            if ($priceSchedules->isNotEmpty()) {
                $earliestStartTime = $priceSchedules->min('start_time');
                PriceSchedule::where('venue_id', $venue->id)
                    ->where('start_time', $earliestStartTime)
                    ->update(['start_time' => $request->operating_hour]);

                $latestEndTime = $priceSchedules->max('end_time');
                PriceSchedule::where('venue_id', $venue->id)
                    ->where('end_time', $latestEndTime)
                    ->update(['end_time' => $request->closing_hour]);
            }



            DB::commit();

            return redirect()->route('venue.index')
                ->with('success', 'Venue berhasil diperbarui!');
        } catch (\Exception $e) {
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
            DB::beginTransaction();

            // Simpan nama file sebelum hapus
            $imageName = $venue->image;

            // Ambil user_id
            $userId = $venue->user_id;

            // Hapus venue
            $venue->delete();

            // Hapus user terkait
            User::destroy($userId);

            DB::commit();

            // Bersihkan file gambar di CMS + FE (di luar transaksi DB)
            if (!empty($imageName)) {
                $this->deleteVenueFile($imageName);
            }

            return redirect()->route('venue.index')
                ->with('success', 'Venue berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showOrders($id)
    {
        $venue = Venue::with(['orders' => function ($q) {
            $q->where('order_type', 'venue')
                ->orderBy('created_at', 'desc');
        }, 'orders.user'])->findOrFail($id);

        return view('dash.admin.venue.detail', compact('venue'));
    }

    public function verifyPayment($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->payment_status !== 'processing') {
            return back()->with('error', 'Order ini tidak dalam status processing.');
        }

        $order->update(['payment_status' => 'paid']);

        return back()->with('success', 'Pembayaran berhasil diverifikasi!');
    }
}
