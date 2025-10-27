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
use Illuminate\Support\Facades\Log;

class AdminVenueController extends Controller
{
    private function uploadImages(array $files): array
    {
        $fePath = base_path('../demo-xanders/images/venue');
        if (!File::exists($fePath)) File::makeDirectory($fePath, 0755, true);

        $filenames = [];
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $ext      = strtolower($file->getClientOriginalExtension());
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBase = Str::slug($origName) ?: 'img';
            $unique   = now()->format('YmdHis') . '-' . Str::random(6);
            $filename = "{$unique}-{$safeBase}.{$ext}";
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

    private function extractGoogleMapsSrc(?string $input): ?string
    {
        if (!$input) return null;
        $input = trim($input);

        if (stripos($input, '<iframe') !== false) {
            if (preg_match('~src\s*=\s*"(.*?)"~i', $input, $m)) return trim($m[1]);
            if (preg_match("~src\s*=\s*'(.*?)'~i", $input, $m)) return trim($m[1]);
            return null;
        }
        return $input;
    }

    private function sanitizeFacilities($input): array
    {
        if (is_array($input)) {
            $items = $input;
        } elseif (is_string($input)) {
            $items = preg_split('/[\r\n,]+/', $input);
        } else {
            $items = [];
        }

        $items = array_map(function ($s) {
            $s = trim(strip_tags((string)$s));
            return mb_substr($s, 0, 100);
        }, $items);

        $items = array_values(array_unique(array_filter($items, fn ($s) => $s !== '')));
        return array_slice($items, 0, 50);
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
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'password'        => 'required|string|min:8',

            'venue_name'      => 'required|string|max:255',
            'address'         => 'required|string',
            'phone'           => 'required|string|max:20',
            'operating_hour'  => 'required|string|max:100',
            'closing_hour'    => 'required|string|max:100',
            'description'     => 'nullable|string',
            'map_embed'       => 'nullable|string|max:5000',

            'images'          => 'nullable|array|max:3',
            'images.*'        => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',

            'facilities'      => 'nullable|array|max:50',
            'facilities.*'    => 'nullable|string|max:100',
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

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'roles'    => 'venue',
            ]);

            $filenames = [];
            if ($request->hasFile('images')) {
                $files = array_slice($request->file('images'), 0, 3);
                $filenames = $this->uploadImages($files);
            }

            $mapSrc     = $this->extractGoogleMapsSrc($request->input('map_embed'));
            $facilities = $this->sanitizeFacilities($request->input('facilities'));

            $venue = Venue::create([
                'user_id'        => $user->id,
                'name'           => $request->venue_name,
                'address'        => $request->address,
                'map_embed'      => $mapSrc,
                'phone'          => $request->phone,
                'operating_hour' => $request->operating_hour,
                'closing_hour'   => $request->closing_hour,
                'description'    => $request->description,
                'rating'         => 0,
                'images'         => $filenames,
                'facilities'     => $facilities,
            ]);

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
            Log::error('Gagal menambah venue', ['e' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage())->withInput();
        }
    }

    public function edit(Venue $venue)
    {
        return view('dash.admin.venue.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        // NB: facilities & images dibuat "sometimes" agar opsional
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required','string','email','max:255', Rule::unique('users')->ignore($venue->user_id)],
            'password'        => 'nullable|string|min:8',

            'venue_name'      => 'required|string|max:255',
            'address'         => 'required|string',
            'phone'           => 'required|string|max:20',
            'operating_hour'  => 'required|string|max:100',
            'closing_hour'    => 'required|string|max:100',
            'description'     => 'nullable|string',
            'map_embed'       => 'nullable|string|max:5000',

            'images'          => 'sometimes|array|max:3',
            'images.*'        => 'sometimes|image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',

            'facilities'      => 'sometimes|array|max:50',
            'facilities.*'    => 'sometimes|string|max:100',
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

            // Update akun pemilik
            $user = User::findOrFail($venue->user_id);
            $user->name  = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // ===== IMAGES (opsional) =====
            if ($request->hasFile('images')) {
                $oldFiles = is_array($venue->images) ? $venue->images : [];
                if (!empty($oldFiles)) $this->deleteImages($oldFiles);

                $files     = array_slice($request->file('images'), 0, 3);
                $filenames = $this->uploadImages($files);
                $venue->images = $filenames;
            }
            // Tidak ada file → jangan sentuh kolom images

            // ===== MAP EMBED =====
            // Hanya diubah kalau field ada (filled boleh kosong untuk clear)
            if ($request->exists('map_embed')) {
                $venue->map_embed = $this->extractGoogleMapsSrc($request->input('map_embed'));
            }

            // ===== FACILITIES (opsional) =====
            if ($request->has('facilities')) {
                $venue->facilities = $this->sanitizeFacilities($request->input('facilities'));
            }

            // ===== Field lain =====
            $venue->name           = $request->venue_name;
            $venue->address        = $request->address;
            $venue->phone          = $request->phone;
            $venue->operating_hour = $request->operating_hour;
            $venue->closing_hour   = $request->closing_hour;
            $venue->description    = $request->description;
            $venue->save();

            // Sinkron jam PriceSchedule (opsional)
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
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal update venue', ['e' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage())->withInput();
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

            if (!empty($imageFilenames)) $this->deleteImages($imageFilenames);

            return redirect()->route('venue.index')->with('success', 'Venue berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus venue', ['e' => $e]);
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
            fputcsv($handle, ['Venue Name','Owner Name','Owner Email','Address','Phone','Operating Hour','Closing Hour','Rating','Images','Facilities','Map Embed']);
            foreach ($venues as $v) {
                $fac = is_array($v->facilities) ? implode('|', $v->facilities) : '';
                fputcsv($handle, [
                    $v->name,
                    optional($v->user)->name,
                    optional($v->user)->email,
                    $v->address,
                    $v->phone,
                    $v->operating_hour ? $v->operating_hour->format('H:i') : '',
                    $v->closing_hour   ? $v->closing_hour->format('H:i') : '',
                    number_format((float)$v->rating, 1),
                    implode('|', (array)($v->images ?? [])),
                    $fac,
                    $v->map_embed ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
