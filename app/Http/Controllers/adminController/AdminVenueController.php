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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class AdminVenueController extends Controller
{
    /**
     * ================= Upload Helper (FE) =================
     * Semua gambar disimpan ke: ../demo-xanders/images/venue
     * FE akan men-display lewat: https://demo-xanders.ptbmn.id/images/venue/{filename}
     */
    private function uploadImages(array $files): array
    {
        $fePath = base_path('../demo-xanders/images/venue');

        if (!File::exists($fePath)) {
            File::makeDirectory($fePath, 0755, true);
        }

        $filenames = [];
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $ext      = strtolower($file->getClientOriginalExtension());
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBase = Str::slug($origName) ?: 'img';
            $unique   = now()->format('YmdHis') . '-' . Str::random(6);
            $filename = "{$unique}-{$safeBase}.{$ext}";

            // Simpan langsung ke ../demo-xanders/images/venue
            $file->move($fePath, $filename);

            $filenames[] = $filename;
        }

        return $filenames;
    }

    private function deleteImages(array $filenames): void
    {
        $fePath = base_path('../demo-xanders/images/venue');

        foreach ($filenames as $file) {
            if (!$file) continue;
            $feFile = $fePath . DIRECTORY_SEPARATOR . $file;
            if (File::exists($feFile)) @unlink($feFile);
        }
    }

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

    public function create()
    {
        return view('dash.admin.venue.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            // Akun pengelola
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'password'        => 'required|string|min:8',

            // Venue
            'venue_name'      => 'required|string|max:255',
            'address'         => 'required|string',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'phone'           => 'required|string|max:20',
            'operating_hour'  => 'required|string|max:100',
            'closing_hour'    => 'required|string|max:100',
            'description'     => 'nullable|string',

            // Gambar
            'images'          => 'nullable|array|max:3',
            'images.*'        => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',
        ]);

        // Validasi jam
        $request->validate([
            'operating_hour' => function ($attribute, $value, $fail) use ($request) {
                if (strtotime($value) >= strtotime($request->closing_hour)) {
                    $fail('Jam operasional harus lebih awal dari jam tutup.');
                }
            },
        ]);

        try {
            DB::beginTransaction();

            // Buat user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'roles'    => 'venue',
            ]);

            // Upload images (maks 3)
            $filenames = [];
            if ($request->hasFile('images')) {
                $files = array_slice($request->file('images'), 0, 3);
                $filenames = $this->uploadImages($files);
            }

            // Simpan venue
            $venue = Venue::create([
                'user_id'        => $user->id,
                'name'           => $request->venue_name,
                'address'        => $request->address,
                'latitude'       => $request->latitude,
                'longitude'      => $request->longitude,
                'phone'          => $request->phone,
                'operating_hour' => $request->operating_hour,
                'closing_hour'   => $request->closing_hour,
                'description'    => $request->description,
                'rating'         => 0,
                'images'         => $filenames,         // array nama file
                'image'          => $filenames[0] ?? null, // jika ada kolom image tunggal
            ]);

            // Price schedule default (opsional)
            PriceSchedule::create([
                'venue_id'          => $venue->id,
                'name'              => 'Reguler AllDay from Admin',
                'price'             => 0,
                'start_time'        => $request->operating_hour,
                'end_time'          => $request->closing_hour,
                'days'              => ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],
                'tables_applicable' => [],
                'is_active'         => true,
            ]);

            DB::commit();

            return redirect()->route('venue.index')->with('success', 'Venue berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal menambah venue', ['e' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Venue $venue)
    {
        return view('dash.admin.venue.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        // Validasi input
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => [
                'required','string','email','max:255',
                Rule::unique('users')->ignore($venue->user_id),
            ],
            'password'        => 'nullable|string|min:8',

            'venue_name'      => 'required|string|max:255',
            'address'         => 'required|string',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'phone'           => 'required|string|max:20',
            'operating_hour'  => 'required|string|max:100',
            'closing_hour'    => 'required|string|max:100',
            'description'     => 'nullable|string',

            'images'          => 'nullable|array|max:3',
            'images.*'        => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',
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

            // Upload gambar baru (jika ada): hapus lama, simpan baru
            if ($request->hasFile('images')) {
                $oldFiles = is_array($venue->images) ? $venue->images : [];
                if (!empty($oldFiles)) {
                    $this->deleteImages($oldFiles);
                }
                $files = array_slice($request->file('images'), 0, 3);
                $filenames = $this->uploadImages($files);
                $venue->images = $filenames;
                $venue->image  = $filenames[0] ?? null; // jika ada kolom image
            } else {
                $venue->images = $venue->images ?? [];
                // $venue->image biarkan apa adanya
            }

            // Update venue fields
            $venue->name           = $request->venue_name;
            $venue->address        = $request->address;
            $venue->latitude       = $request->latitude;
            $venue->longitude      = $request->longitude;
            $venue->phone          = $request->phone;
            $venue->operating_hour = $request->operating_hour;
            $venue->closing_hour   = $request->closing_hour;
            $venue->description    = $request->description;
            $venue->save();

            // Sinkron jam di price schedule (opsional)
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

            return redirect()->route('venue.index')->with('success', 'Venue berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal update venue', ['e' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Venue $venue)
    {
        try {
            DB::beginTransaction();

            $imageFilenames = is_array($venue->images) ? $venue->images : [];
            $userId = $venue->user_id;

            $venue->delete();
            User::destroy($userId);

            DB::commit();

            // Hapus file di FE
            if (!empty($imageFilenames)) {
                $this->deleteImages($imageFilenames);
            }

            return redirect()->route('venue.index')->with('success', 'Venue berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal hapus venue', ['e' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showOrders($id)
    {
        $venue = Venue::with([
            'orders' => function ($q) {
                $q->where('order_type', 'venue')->orderBy('created_at', 'desc');
            },
            'orders.user'
        ])->findOrFail($id);

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

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $venues = Venue::with('user')
            ->when(request('search'), function ($query) {
                $search = request('search');
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
            })->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="venues.csv"',
        ];

        $callback = function () use ($venues) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Venue Name','Owner Name','Owner Email','Address','Phone','Operating Hour','Closing Hour','Rating','Images']);
            foreach ($venues as $v) {
                fputcsv($handle, [
                    $v->name,
                    optional($v->user)->name,
                    optional($v->user)->email,
                    $v->address,
                    $v->phone,
                    $v->operating_hour,
                    $v->closing_hour,
                    number_format((float)$v->rating, 1),
                    implode('|', (array)($v->images ?? [])),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
